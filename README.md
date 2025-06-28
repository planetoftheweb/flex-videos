# Flex Videos Plugin

A simple WordPress plugin to add flexible, responsive video embeds to your site.

## Features
- Responsive video grid for YouTube, Vimeo, and more
- Control the number of columns and thumbnail width via shortcode attributes
- Easy to use: just activate and use the provided shortcodes or blocks
- Google API integration for YouTube data
- Caching for improved performance
- Admin options for configuration and cache reset

## Installation
You can install the plugin using either the WordPress admin or FTP:

### Method 1: WordPress Admin (Recommended)
1. Download the repository as a ZIP file from GitHub (click the green **Code** button, then **Download ZIP**).
2. In your WordPress admin, go to **Plugins > Add New** and click **Upload Plugin** at the top.
3. Select the ZIP file you downloaded and click **Install Now**.
4. Once installed, click **Activate Plugin**.
5. After activation, proceed to the setup steps below to configure your API key and other settings.

### Method 2: FTP/SFTP
1. Download the repository as a ZIP file from GitHub and extract it on your computer.
2. Using an FTP or SFTP client, upload the extracted plugin folder (e.g., `flex-videos-plugin`) to the `/wp-content/plugins/` directory on your server.
3. In your WordPress admin, go to **Plugins** and find **Flex Videos Plugin** in the list.
4. Click **Activate**.
5. After activation, proceed to the setup steps below to configure your API key and other settings.

## Setup & Configuration

### A. Google Cloud Setup (YouTube API Key)
1. Go to the [Google Cloud Console](https://console.cloud.google.com/).
2. Create or select a project.
3. Navigate to **APIs & Services > Credentials**.
4. Click **Create Credentials > API key** and copy the key.
5. Go to **APIs & Services > Library** and enable the **YouTube Data API v3** for your project.

### B. Plugin Configuration in WordPress
1. In your WordPress admin, go to **Settings > Flex Videos**.
2. Paste your Google API Key into the appropriate field.
3. Save changes.
4. (Optional) Use the **Reset Cache** button if you need to clear cached video data.

### Using the Plugin
- Use the `[flex_video url="..."]` shortcode to embed a video responsively.
- Example: `[flex_video url="https://www.youtube.com/watch?v=xxxxxxx"]`
- To display a grid of videos, use the `[flex_videos columns="3" width="320px"]` shortcode attributes:
  - `columns`: Number of columns in the grid (default: 3)
  - `width`: Maximum width of each video thumbnail (default: 320px)
- Example: `[flex_videos columns="4" width="250px"]`
- The plugin supports YouTube, Vimeo, and other major platforms.

#### Example CSS (included by default)
The following CSS is automatically included by the plugin. If you want to customize the appearance of the video grid or thumbnails, you can override these styles in your theme's custom CSS or in the WordPress Customizer (Appearance > Customize > Additional CSS). Use the same class names as shown below:

```css
.flex-videos-grid {
  display: grid;
  grid-template-columns: repeat(var(--flex-videos-columns, 3), 1fr);
  gap: 20px;
  padding: 20px 0;
}
.flex-videos-item {
  aspect-ratio: 16/9;
  max-width: var(--flex-videos-width, 320px);
  margin: 0 auto;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,.1);
  background: #000;
  transition: transform .3s, box-shadow .3s;
}
.flex-videos-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0,0,0,.15);
}
.flex-videos-item iframe {
  width: 100%;
  height: 100%;
  border: 0;
  display: block;
}
```

## Contributing
Pull requests are welcome! For major changes, please open an issue first to discuss what you would like to change.

## License
[MIT](LICENSE)
