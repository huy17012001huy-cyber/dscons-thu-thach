using System.Reflection;
using System.Security;
using System.Windows.Forms;

namespace DSCons.Revit.Installer;

internal static class Program
{
    private const string Launcher = "DSCons.Revit.Launcher";
    private static readonly IReadOnlyDictionary<string, string> SourceByVersion = new Dictionary<string, string>
    {
        ["2019"] = "2019", ["2020"] = "2019",
        ["2021"] = "2021", ["2022"] = "2021",
        ["2023"] = "2023", ["2024"] = "2023",
        ["2025"] = "2025", ["2026"] = "2025",
        ["2027"] = "2027",
    };

    [STAThread]
    private static void Main(string[] args)
    {
        var silent = args.Any(arg => string.Equals(arg, "--silent", StringComparison.OrdinalIgnoreCase));
        try
        {
            var apiBaseUrl = ReadApiBaseUrl(args);
            var installed = Install(apiBaseUrl);
            var message = installed.Count == 0
                ? "Không tìm thấy Revit 2019–2027 trên máy này."
                : "Đã cài Ribbon DSCons cho Revit: " + string.Join(", ", installed) + ".\n\nMở Revit và chọn tab DSCons để đăng nhập.";
            if (!silent) MessageBox.Show(message, "DSCons Revit Launcher", MessageBoxButtons.OK, MessageBoxIcon.Information);
        }
        catch (Exception exception)
        {
            if (!silent) MessageBox.Show("Không thể cài DSCons Revit Launcher.\n\n" + exception.Message, "DSCons Revit Launcher", MessageBoxButtons.OK, MessageBoxIcon.Error);
            Environment.ExitCode = 1;
        }
    }

    private static List<string> Install(string apiBaseUrl)
    {
        var installed = new List<string>();
        var assembly = Assembly.GetExecutingAssembly();
        foreach (var version in SourceByVersion.Keys)
        {
            var revitExe = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ProgramFiles), "Autodesk", "Revit " + version, "Revit.exe");
            if (!File.Exists(revitExe)) continue;

            var target = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "DSCons", "RevitLauncher", "versions", version);
            Directory.CreateDirectory(target);
            ExtractPayload(assembly, SourceByVersion[version], target);
            File.WriteAllText(Path.Combine(target, Launcher + ".config"), "DSCONS_API_BASE_URL=" + apiBaseUrl);

            var addins = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "Autodesk", "Revit", "Addins", version);
            Directory.CreateDirectory(addins);
            var dll = Path.Combine(target, Launcher + ".dll");
            var manifest = $"""
                <?xml version="1.0" encoding="utf-8" standalone="no"?>
                <RevitAddIns>
                  <AddIn Type="Application">
                    <Name>DSCons Revit Launcher</Name>
                    <Assembly>{SecurityElement.Escape(dll)}</Assembly>
                    <AddInId>8B4F7C0D-7970-4A5F-9B8B-8B2A1D4D1352</AddInId>
                    <FullClassName>DSCons.Revit.Launcher.App</FullClassName>
                    <VendorId>DSCONS</VendorId>
                    <VendorDescription>DSCons Viet Nam</VendorDescription>
                  </AddIn>
                </RevitAddIns>
                """;
            File.WriteAllText(Path.Combine(addins, Launcher + ".addin"), manifest);
            installed.Add(version);
        }
        return installed;
    }

    private static void ExtractPayload(Assembly assembly, string sourceVersion, string destination)
    {
        // MSBuild normalizes the hyphen in the folder name to an underscore
        // when it generates embedded-resource names.
        // Resource names follow the project root namespace, while the
        // published setup EXE deliberately has a user-friendly file name.
        const string resourceNamespace = "DSCons.Revit.Installer.Payload.";
        var prefix = resourceNamespace + "revit_" + sourceVersion + ".";
        var resources = assembly.GetManifestResourceNames().Where(name => name.StartsWith(prefix, StringComparison.Ordinal)).ToArray();
        if (resources.Length == 0) throw new InvalidOperationException("Thiếu payload cho Revit " + sourceVersion + ".");

        foreach (var resource in resources)
        {
            var name = resource[prefix.Length..];
            using var input = assembly.GetManifestResourceStream(resource) ?? throw new InvalidOperationException("Không đọc được payload.");
            using var output = File.Create(Path.Combine(destination, name));
            input.CopyTo(output);
        }
    }

    private static string ReadApiBaseUrl(string[] args)
    {
        var value = args.SkipWhile(arg => !string.Equals(arg, "--api-url", StringComparison.OrdinalIgnoreCase)).Skip(1).FirstOrDefault()
            ?? "http://localhost:8080";
        if (!Uri.TryCreate(value, UriKind.Absolute, out var uri) || (uri.Scheme != Uri.UriSchemeHttps && !string.Equals(uri.Host, "localhost", StringComparison.OrdinalIgnoreCase)))
            throw new InvalidOperationException("URL DSCons không hợp lệ. Dùng HTTPS khi phát hành.");
        return value.TrimEnd('/');
    }
}
