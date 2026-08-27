using System;
using System.Collections;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Net.Http;
using System.Runtime.Serialization.Json;
using System.Text;

namespace DSCons.Revit.Launcher;

internal static class LauncherApi
{
    internal static bool StartLogin(string revitVersion, out string message)
    {
        message = string.Empty;
        if (!LicenseState.HasValidApiBaseUrl)
        {
            message = "Chưa cấu hình DSCons.Revit.Launcher.config với domain DSCons hợp lệ.";
            return false;
        }

        try
        {
            var response = Post("/api/revit/device/start", new Dictionary<string, string>
            {
                ["installation_id"] = LicenseState.InstallationId,
                ["device_fingerprint"] = LicenseState.DeviceFingerprint,
                ["device_label"] = Environment.MachineName,
                ["revit_version"] = revitVersion,
                ["client_version"] = "0.1.0",
            });
            var code = Value(response, "authorization_code");
            var url = Value(response, "verification_url");
            if (string.IsNullOrWhiteSpace(code) || string.IsNullOrWhiteSpace(url))
            {
                message = "Không tạo được mã kích hoạt. Hãy thử lại sau.";
                return false;
            }

            LicenseState.PendingCode = code;
            Process.Start(new ProcessStartInfo(url) { UseShellExecute = true });
            message = "Trình duyệt đã mở. Đăng nhập DSCons rồi bấm xác nhận, sau đó quay lại Revit và bấm Làm mới quyền.";
            return true;
        }
        catch (Exception)
        {
            message = "Không thể kết nối DSCons. Kiểm tra mạng và cấu hình domain rồi thử lại.";
            return false;
        }
    }

    internal static bool RefreshEntitlements(string revitVersion, out string message)
    {
        message = string.Empty;
        if (!LicenseState.HasValidApiBaseUrl)
        {
            message = "Chưa cấu hình domain DSCons hợp lệ.";
            return false;
        }

        try
        {
            var pendingCode = LicenseState.PendingCode;
            if (!string.IsNullOrWhiteSpace(pendingCode))
            {
                var poll = Post("/api/revit/device/poll", new Dictionary<string, string> { ["authorization_code"] = pendingCode! });
                var status = Value(poll, "status");
                if (status == "pending")
                {
                    message = "Bạn chưa xác nhận trong trình duyệt. Hãy hoàn tất bước xác nhận rồi bấm lại.";
                    return false;
                }
                var accessToken = Value(poll, "access_token");
                if (status != "approved" || string.IsNullOrWhiteSpace(accessToken))
                {
                    message = Value(poll, "message") ?? "Mã kích hoạt đã hết hạn hoặc bị từ chối. Hãy đăng nhập lại.";
                    LicenseState.PendingCode = null;
                    return false;
                }
                LicenseState.SaveToken(accessToken!);
                LicenseState.PendingCode = null;
            }

            var token = LicenseState.Token;
            if (string.IsNullOrWhiteSpace(token))
            {
                message = "Hãy bấm Đăng nhập DSCons trước.";
                return false;
            }
            var entitlements = Get("/api/revit/entitlements", token!);
            LicenseState.SaveTools(ToolKeys(entitlements, "tools"));
            LicenseState.MarkHeartbeat();
            message = "Đã làm mới quyền tool DSCons.";
            return true;
        }
        catch (Exception)
        {
            message = "Không thể làm mới quyền. Nếu bạn đang offline, tool chỉ dùng được trong thời gian cho phép sau lần kiểm tra gần nhất.";
            return false;
        }
    }

    internal static bool TryHeartbeat(string revitVersion)
    {
        var token = LicenseState.Token;
        if (string.IsNullOrWhiteSpace(token) || !LicenseState.HasValidApiBaseUrl) return false;
        try
        {
            var result = Post("/api/revit/heartbeat", new Dictionary<string, string>
            {
                ["revit_version"] = revitVersion,
                ["client_version"] = "0.1.0",
            }, token);
            var replacement = Value(result, "replacement_token");
            if (!string.IsNullOrWhiteSpace(replacement)) LicenseState.SaveToken(replacement!);
            LicenseState.SaveTools(ToolKeys(result, "entitlements"));
            LicenseState.MarkHeartbeat();
            return true;
        }
        catch { return false; }
    }

    internal static bool CanRun(string toolKey, string revitVersion, out string message)
    {
        if (TryHeartbeat(revitVersion) && LicenseState.HasTool(toolKey))
        {
            message = string.Empty;
            return true;
        }
        if (LicenseState.HasTool(toolKey) && LicenseState.HasOfflineGrace())
        {
            message = string.Empty;
            return true;
        }
        message = "Tool chưa được cấp quyền hoặc license cần được kích hoạt lại. Bấm Đăng nhập DSCons / Làm mới quyền, hoặc mua tool trong Marketplace DSCons.";
        return false;
    }

    internal static bool Logout(out string message)
    {
        message = string.Empty;
        var token = LicenseState.Token;
        if (string.IsNullOrWhiteSpace(token)) { LicenseState.Clear(); return true; }
        try
        {
            Post("/api/revit/logout", new Dictionary<string, string>(), token);
            LicenseState.Clear();
            message = "Đã đăng xuất thiết bị Revit. Bạn có thể kích hoạt máy khác.";
            return true;
        }
        catch
        {
            message = "Không thể đăng xuất khi đang mất kết nối. Hãy thử lại khi có mạng hoặc đăng xuất thiết bị tại website DSCons.";
            return false;
        }
    }

    private static Dictionary<string, object> Get(string path, string token) => Send(HttpMethod.Get, path, null, token);
    private static Dictionary<string, object> Post(string path, Dictionary<string, string> payload, string? token = null) => Send(HttpMethod.Post, path, payload, token);
    private static Dictionary<string, object> Send(HttpMethod method, string path, Dictionary<string, string>? payload, string? token)
    {
        using (var client = new HttpClient { Timeout = TimeSpan.FromSeconds(8) })
        using (var request = new HttpRequestMessage(method, new Uri(LicenseState.ApiBaseUrl.TrimEnd('/') + path)))
        {
            if (!string.IsNullOrWhiteSpace(token)) request.Headers.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", token);
            if (payload != null) request.Content = new StringContent(Serialize(payload), Encoding.UTF8, "application/json");
            using (var response = client.SendAsync(request).GetAwaiter().GetResult())
            {
                var body = response.Content.ReadAsStringAsync().GetAwaiter().GetResult();
                var data = string.IsNullOrWhiteSpace(body) ? new Dictionary<string, object>() : Deserialize(body);
                if (!response.IsSuccessStatusCode) throw new InvalidOperationException(Value(data, "message") ?? "DSCons đã từ chối yêu cầu.");
                return data;
            }
        }
    }

    private static string Serialize(Dictionary<string, string> value)
    {
        var serializer = new DataContractJsonSerializer(typeof(Dictionary<string, string>));
        using (var stream = new MemoryStream())
        {
            serializer.WriteObject(stream, value);
            return Encoding.UTF8.GetString(stream.ToArray());
        }
    }

    private static Dictionary<string, object> Deserialize(string value)
    {
        var serializer = new DataContractJsonSerializer(typeof(Dictionary<string, object>));
        using (var stream = new MemoryStream(Encoding.UTF8.GetBytes(value)))
        {
            return serializer.ReadObject(stream) as Dictionary<string, object>
                ?? throw new InvalidOperationException("DSCons trả về dữ liệu không hợp lệ.");
        }
    }

    private static string? Value(IDictionary<string, object> data, string key)
        => data.ContainsKey(key) && data[key] != null ? Convert.ToString(data[key]) : null;
    private static IEnumerable<string> ToolKeys(IDictionary<string, object> data, string key)
    {
        if (!data.ContainsKey(key) || !(data[key] is IEnumerable tools)) return Enumerable.Empty<string>();
        return tools.Cast<object>()
            .OfType<IDictionary<string, object>>()
            .Select(tool => Value(tool, "tool_key"))
            .Where(value => !string.IsNullOrWhiteSpace(value))
            .Select(value => value!);
    }
}
