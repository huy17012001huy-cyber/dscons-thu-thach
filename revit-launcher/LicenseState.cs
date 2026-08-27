using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Security.Cryptography;
using System.Text;

namespace DSCons.Revit.Launcher;

internal static class LicenseState
{
    private static readonly string Root = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "DSCons", "RevitLauncher");
    private static readonly string TokenFile = Path.Combine(Root, "session.bin");
    private static readonly string PendingFile = Path.Combine(Root, "pending.txt");
    private static readonly string EntitlementsFile = Path.Combine(Root, "tools.txt");
    private static readonly string HeartbeatFile = Path.Combine(Root, "heartbeat.txt");
    internal static string InstallationId => ReadOrCreate("installation.txt", Guid.NewGuid().ToString("N"));
    internal static string DeviceFingerprint => Hash(Environment.MachineName + "|" + Environment.UserDomainName + "|" + Environment.OSVersion.VersionString);
    internal static string? Token => ReadProtected(TokenFile);
    internal static string? PendingCode { get => ReadText(PendingFile); set => WriteText(PendingFile, value); }
    internal static bool HasTool(string key) => (ReadText(EntitlementsFile) ?? string.Empty).Split(new[] { '\n' }, StringSplitOptions.RemoveEmptyEntries).Contains(key, StringComparer.Ordinal);
    internal static void SaveToken(string token) => WriteProtected(TokenFile, token);
    internal static void SaveTools(IEnumerable<string> keys) => WriteText(EntitlementsFile, string.Join("\n", keys));
    internal static DateTime? LastHeartbeatUtc { get { DateTime value; return DateTime.TryParse(ReadText(HeartbeatFile), null, System.Globalization.DateTimeStyles.RoundtripKind, out value) ? value.ToUniversalTime() : (DateTime?)null; } }
    internal static bool HasOfflineGrace() => LastHeartbeatUtc.HasValue && LastHeartbeatUtc.Value >= DateTime.UtcNow.AddHours(-48);
    internal static void MarkHeartbeat() => WriteText(HeartbeatFile, DateTime.UtcNow.ToString("o"));
    internal static void Clear() { SafeDelete(TokenFile); SafeDelete(PendingFile); SafeDelete(EntitlementsFile); SafeDelete(HeartbeatFile); }
    internal static string ApiBaseUrl => ReadConfig("DSCONS_API_BASE_URL") ?? "https://YOUR-DSCONS-DOMAIN";
    internal static bool HasValidApiBaseUrl => Uri.TryCreate(ApiBaseUrl, UriKind.Absolute, out var uri)
        && (uri.Scheme == Uri.UriSchemeHttps || uri.Host.Equals("localhost", StringComparison.OrdinalIgnoreCase))
        && !uri.Host.Contains("YOUR-DSCONS-DOMAIN");
    internal static void EnsureDirectory() { Directory.CreateDirectory(Root); }
    private static string ReadOrCreate(string name, string value) { var path = Path.Combine(Root, name); var current = ReadText(path); if (!string.IsNullOrWhiteSpace(current)) return current!; WriteText(path, value); return value; }
    private static string? ReadConfig(string key) { var path = Path.Combine(Path.GetDirectoryName(typeof(LicenseState).Assembly.Location) ?? string.Empty, "DSCons.Revit.Launcher.config"); if (!File.Exists(path)) return null; return File.ReadAllLines(path).Select(x => x.Split(new[] { '=' }, 2)).Where(x => x.Length == 2 && x[0].Trim() == key).Select(x => x[1].Trim().TrimEnd('/')).FirstOrDefault(); }
    private static string? ReadText(string path) { try { return File.Exists(path) ? File.ReadAllText(path).Trim() : null; } catch { return null; } }
    private static void WriteText(string path, string? value) { EnsureDirectory(); File.WriteAllText(path, value ?? string.Empty); }
    private static void WriteProtected(string path, string text) { EnsureDirectory(); File.WriteAllBytes(path, ProtectedData.Protect(Encoding.UTF8.GetBytes(text), null, DataProtectionScope.CurrentUser)); }
    private static string? ReadProtected(string path) { try { return File.Exists(path) ? Encoding.UTF8.GetString(ProtectedData.Unprotect(File.ReadAllBytes(path), null, DataProtectionScope.CurrentUser)) : null; } catch { return null; } }
    private static void SafeDelete(string path) { try { if (File.Exists(path)) File.Delete(path); } catch { } }
    private static string Hash(string value) { using var sha = SHA256.Create(); return BitConverter.ToString(sha.ComputeHash(Encoding.UTF8.GetBytes(value))).Replace("-", string.Empty).ToLowerInvariant(); }
}
