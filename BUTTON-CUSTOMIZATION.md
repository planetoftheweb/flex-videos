# Button Customization Guide

The Flex Videos plugin provides two ways to customize the "Visit Channel" button colors to match your site's theme.

## Method 1: Admin Settings (Recommended)

The easiest way to customize button colors is through the WordPress admin:

1. Go to **Settings → Flex Videos**
2. Scroll down to **Channel Link Options** section
3. Use the color pickers to customize:
   - **Button Background Color** - The main button color
   - **Button Hover Color** - Color when users hover over the button
   - **Button Text Color** - The text color inside the button
4. Click **Save Settings**

## Method 2: Custom CSS (Advanced)

For advanced users who want more control, you can still use CSS custom properties in your theme's `style.css` file or in **Appearance > Customize > Additional CSS**:

### Example 1: Blue Theme
```css
:root {
    --flex-videos-button-bg: #0073aa;
    --flex-videos-button-bg-hover: #005a87;
    --flex-videos-button-color: #fff;
    --flex-videos-button-shadow: rgba(0, 115, 170, 0.3);
    --flex-videos-button-shadow-hover: rgba(0, 115, 170, 0.4);
}
```

### Example 2: Green Theme
```css
:root {
    --flex-videos-button-bg: #28a745;
    --flex-videos-button-bg-hover: #20c997;
    --flex-videos-button-color: #fff;
    --flex-videos-button-shadow: rgba(40, 167, 69, 0.3);
    --flex-videos-button-shadow-hover: rgba(40, 167, 69, 0.4);
}
```

### Example 3: Red Button
```css
:root {
    --flex-videos-button-bg: #dc3545;
    --flex-videos-button-bg-hover: #c82333;
    --flex-videos-button-color: #fff;
    --flex-videos-button-shadow: rgba(220, 53, 69, 0.3);
    --flex-videos-button-shadow-hover: rgba(220, 53, 69, 0.4);
}
```

**Note:** When using Method 2 (CSS), the custom CSS will override the admin settings.

## Advanced Customization

For complete control, you can target the button directly:

```css
.flex-videos-channel-link a {
    background: your-custom-color !important;
    color: your-text-color !important;
    border-radius: 4px; /* Change button shape */
    font-size: 16px; /* Change text size */
    padding: 10px 15px; /* Change button size */
}
```

## Custom Properties Reference

| Property | Description | Default |
|----------|-------------|---------|
| `--flex-videos-button-bg` | Button background | Orange gradient |
| `--flex-videos-button-bg-hover` | Button background on hover | Darker orange gradient |
| `--flex-videos-button-color` | Text color | White |
| `--flex-videos-button-shadow` | Button shadow | Orange shadow |
| `--flex-videos-button-shadow-hover` | Button shadow on hover | Darker orange shadow |
