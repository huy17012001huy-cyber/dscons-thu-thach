using Autodesk.Revit.Attributes;
using Autodesk.Revit.DB;
using Autodesk.Revit.UI;

namespace DSCons.Revit.Launcher.Commands;

[Transaction(TransactionMode.Manual)]
public sealed class LoginCommand : IExternalCommand
{
    public Result Execute(ExternalCommandData data, ref string message, ElementSet elements)
    {
        string result;
        LauncherApi.StartLogin(data.Application.Application.VersionNumber, out result);
        TaskDialog.Show("DSCons", result);
        return Result.Succeeded;
    }
}

[Transaction(TransactionMode.Manual)]
public sealed class RefreshEntitlementsCommand : IExternalCommand
{
    public Result Execute(ExternalCommandData data, ref string message, ElementSet elements)
    {
        string result;
        LauncherApi.RefreshEntitlements(data.Application.Application.VersionNumber, out result);
        TaskDialog.Show("DSCons", result);
        return Result.Succeeded;
    }
}

[Transaction(TransactionMode.Manual)]
public sealed class LogoutCommand : IExternalCommand
{
    public Result Execute(ExternalCommandData data, ref string message, ElementSet elements)
    {
        string result;
        LauncherApi.Logout(out result);
        TaskDialog.Show("DSCons", result);
        return Result.Succeeded;
    }
}

[Transaction(TransactionMode.Manual)]
public sealed class TestOneCommand : LicensedDemoCommand
{
    protected override string ToolKey => "dscons-test-1";
    protected override string SuccessMessage => "DSCons Tool Test 1 đã load thành công.";
}

[Transaction(TransactionMode.Manual)]
public sealed class TestTwoCommand : LicensedDemoCommand
{
    protected override string ToolKey => "dscons-test-2";
    protected override string SuccessMessage => "DSCons Tool Test 2 đã load thành công.";
}

public abstract class LicensedDemoCommand : IExternalCommand
{
    protected abstract string ToolKey { get; }
    protected abstract string SuccessMessage { get; }

    public Result Execute(ExternalCommandData data, ref string message, ElementSet elements)
    {
        string reason;
        if (!LauncherApi.CanRun(ToolKey, data.Application.Application.VersionNumber, out reason))
        {
            TaskDialog.Show("DSCons", reason);
            return Result.Cancelled;
        }
        TaskDialog.Show("DSCons", SuccessMessage);
        return Result.Succeeded;
    }
}

public sealed class TestOneAvailability : IExternalCommandAvailability
{
    public bool IsCommandAvailable(UIApplication applicationData, CategorySet selectedCategories) => LicenseState.HasTool("dscons-test-1");
}

public sealed class TestTwoAvailability : IExternalCommandAvailability
{
    public bool IsCommandAvailable(UIApplication applicationData, CategorySet selectedCategories) => LicenseState.HasTool("dscons-test-2");
}
