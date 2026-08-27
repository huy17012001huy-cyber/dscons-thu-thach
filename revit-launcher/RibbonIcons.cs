using System.Windows.Media;

namespace DSCons.Revit.Launcher;

internal static class RibbonIcons
{
    private static readonly Brush Blue = new SolidColorBrush(Color.FromRgb(31, 119, 190));
    private static readonly Brush Orange = new SolidColorBrush(Color.FromRgb(243, 148, 2));

    internal static ImageSource Login() => Draw(Blue,
        "M12,4 A4,4 0 1 1 11.99,4 M4,22 A8,8 0 0 1 14,15 M14,18 L22,18 M19,15 L22,18 L19,21");

    internal static ImageSource Refresh() => Draw(Blue,
        "M20,10 A8,8 0 0 0 6,7 M6,7 L6,2 M6,7 L1,7 M4,14 A8,8 0 0 0 18,17 M18,17 L18,22 M18,17 L23,17");

    internal static ImageSource Logout() => Draw(Blue,
        "M10,3 L5,3 A2,2 0 0 0 3,5 L3,19 A2,2 0 0 0 5,21 L10,21 M13,8 L18,12 L13,16 M18,12 L8,12");

    internal static ImageSource TestOne() => Draw(Orange,
        "M12,3 A9,9 0 1 1 11.99,3 M12,8 L12,16 M9,10 L12,8 L15,10");

    internal static ImageSource TestTwo() => Draw(Orange,
        "M12,3 A9,9 0 1 1 11.99,3 M8,12 L11,15 L16,9");

    private static ImageSource Draw(Brush brush, string geometry)
    {
        var drawing = new GeometryDrawing(null, new Pen(brush, 1.9), Geometry.Parse(geometry));
        var image = new DrawingImage(drawing);
        image.Freeze();
        return image;
    }
}
