using Autodesk.Revit.UI;
using DSCons.Revit.Launcher.Commands;

namespace DSCons.Revit.Launcher;

public sealed class App : IExternalApplication
{
    public Result OnStartup(UIControlledApplication application)
    {
        const string tab = "DSCons";
        try { application.CreateRibbonTab(tab); } catch { }
        var account = application.CreateRibbonPanel(tab, "Tài khoản");
        account.AddItem(Button("DsconsLogin", "Đăng nhập\nDSCons", typeof(LoginCommand), RibbonIcons.Login(), "Kết nối Revit với tài khoản DSCons."));
        account.AddItem(Button("DsconsRefresh", "Làm mới\nquyền", typeof(RefreshEntitlementsCommand), RibbonIcons.Refresh(), "Tải lại các tool Revit bạn đã mua."));
        account.AddItem(Button("DsconsLogout", "Đăng xuất\nthiết bị", typeof(LogoutCommand), RibbonIcons.Logout(), "Đăng xuất thiết bị này để có thể đổi máy."));

        var tools = application.CreateRibbonPanel(tab, "Tool đã mua");
        var test1 = Button("DsconsTest1", "Test\n1", typeof(TestOneCommand), RibbonIcons.TestOne(), "Chạy DSCons Tool Test 1.");
        test1.AvailabilityClassName = typeof(TestOneAvailability).FullName;
        var test2 = Button("DsconsTest2", "Test\n2", typeof(TestTwoCommand), RibbonIcons.TestTwo(), "Chạy DSCons Tool Test 2.");
        test2.AvailabilityClassName = typeof(TestTwoAvailability).FullName;
        tools.AddItem(test1); tools.AddItem(test2);
        // A heartbeat failure must never prevent Revit from starting. The command
        // itself still checks the entitlement again before it runs.
        LauncherApi.TryHeartbeat(application.ControlledApplication.VersionNumber);
        return Result.Succeeded;
    }
    public Result OnShutdown(UIControlledApplication application) => Result.Succeeded;

    private static PushButtonData Button(string id, string label, System.Type command, System.Windows.Media.ImageSource icon, string tooltip)
        => new PushButtonData(id, label, typeof(App).Assembly.Location, command.FullName)
        {
            Image = icon,
            LargeImage = icon,
            ToolTip = tooltip,
            LongDescription = tooltip,
        };
}
