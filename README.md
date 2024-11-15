# Brands for WooCommerce®

Allows you to create and manage brands in WooCommerce, with options to display brands as lists, thumbnails, or sidebar widgets.

## Table of Contents

1. [Features](#features)
2. [Installation](#installation)
3. [Activation](#activation)
4. [Usage](#usage)
    - [Managing Brands](#managing-brands)
    - [Shortcodes](#shortcodes)
        - [1. Display Brands in a Grid](#1-display-brands-in-a-grid)
        - [2. Display Products by Brand](#2-display-products-by-brand)
        - [3. Display an A-Z Indexed List of Brands](#3-display-an-a-z-indexed-list-of-brands)
5. [Widgets](#widgets)
    - [Brand Thumbnails Widget](#brand-thumbnails-widget)
6. [REST API](#rest-api)
7. [Customization](#customization)
8. [Frequently Asked Questions](#frequently-asked-questions)
9. [Support](#support)
10. [Changelog](#changelog)
11. [License](#license)

## Features

- **Manage Brands:** Create, edit, and delete brands associated with WooCommerce products.
- **Brand Images & URLs:** Assign images and website URLs to each brand.
- **Display Options:** Display brands in a grid layout, as a list with A-Z indexing, or using widgets.
- **Shortcodes:** Utilize shortcodes to embed brand lists and product grids within pages and posts.
- **REST API Integration:** Access brand data via REST API endpoints.
- **Widget Support:** Easily add brand thumbnails to your site's sidebar or other widget-ready areas.
- **Automatic Updates:** Stay updated with the latest features and improvements via GitHub integration.

## Installation

### 1. Download the Plugin

- **Option 1:** Download the plugin as a ZIP file from the [GitHub repository](https://github.com/robertdevore/brands-for-woocommerce/).
- **Option 2:** Clone the repository using Git:
```
git clone https://github.com/robertdevore/brands-for-woocommerce.git
```

### 2. Upload the Plugin

- **Via WordPress Dashboard:**

    1. Log in to your WordPress admin dashboard.
    2. Navigate to **Plugins > Add New**.
    3. Click on **Upload Plugin**.
    4. Choose the downloaded `brands-for-woocommerce.zip` file.
    5. Click **Install Now**.
    6. After installation, click **Activate Plugin**.
- **Via FTP:**

    1. Extract the downloaded ZIP file.
    2. Upload the extracted folder (`brands-for-woocommerce`) to the `/wp-content/plugins/` directory on your server.
    3. Log in to your WordPress admin dashboard.
    4. Navigate to **Plugins > Installed Plugins**.
    5. Find **Brands for WooCommerce®** and click **Activate**.

### 3. Verify Installation

After activation, ensure that the plugin is active by navigating to **Plugins > Installed Plugins** and verifying that **Brands for WooCommerce®** is listed as active.

## Activation

Upon activation, the plugin:

- Registers a new taxonomy `product_brand` associated with WooCommerce products.
- Adds custom fields for brand images and website URLs.
- Registers shortcodes and widgets for displaying brands and products.
- Sets up REST API endpoints for accessing brand data.

## Usage

### Managing Brands

1. **Create a New Brand:**

    - Navigate to **Products > Brands** in your WordPress admin dashboard.
    - Click **Add New Brand**.
    - Enter the **Brand Name**.
    - Upload a **Brand Image** by clicking the **Upload Image** button.
    - Enter the **Brand Website URL**.
    - Click **Add New Brand** to save.
2. **Edit an Existing Brand:**

    - Navigate to **Products > Brands**.
    - Hover over the brand you wish to edit and click **Edit**.
    - Update the **Brand Name**, **Brand Image**, or **Brand Website URL** as needed.
    - Click **Update** to save changes.
3. **Delete a Brand:**

    - Navigate to **Products > Brands**.
    - Hover over the brand you wish to delete and click **Delete**.
    - Confirm the deletion when prompted.

### Shortcodes

The plugin provides three shortcodes to display brands and products associated with brands. Below are detailed instructions on how to use each shortcode.

#### 1. Display Brands in a Grid

**Shortcode:** `[product_brand]`

**Description:** Displays all brands in a customizable grid layout.

**Attributes:**

- `columns` (int): Number of columns in the grid. Default is `4`.
- `show_title` (bool): Show or hide the brand title. Default is `true`.
- `link_image` (bool): Enable or disable linking the brand image to the brand archive page. Default is `true`.
- `show_description` (bool): Show or hide the brand description. Default is `false`.

**Usage Examples:**

- **Default Grid (4 columns, titles shown, images linked, descriptions hidden):**
```
[product_brand]
```

- **Two-Column Grid with Descriptions:**
```
[product_brand columns="2" show_description="true"]
```

- **Four-Column Grid without Image Links:**
```
[product_brand link_image="false"]
```

**Example Output:**

A responsive grid displaying brand images and names. Clicking on an image navigates to the respective brand archive page.

#### 2. Display Products by Brand

**Shortcode:** `[brand_products]`

**Description:** Displays WooCommerce products associated with a specific brand in a grid layout.

**Attributes:**

- `brand` (string): The slug of the brand to display products from. **(Required)**
- `per_page` (int): Number of products to display. Default is `12`.
- `columns` (int): Number of columns in the grid. Default is `4`.
- `orderby` (string): Field to order products by. Default is `title`. Options include `date`, `price`, etc.
- `order` (string): Order direction. `asc` for ascending, `desc` for descending. Default is `asc`.

**Usage Examples:**

- **Display 8 Products from "brand-a" in a 3-column grid:**
```
[brand_products brand="brand-a" per_page="8" columns="3"]
```

- **Display 16 Products from "brand-b" ordered by date in descending order:**
```
[brand_products brand="brand-b" per_page="16" orderby="date" order="desc"]
```

**Example Output:**

A WooCommerce-styled grid showcasing products from the specified brand, adhering to the provided layout and ordering parameters.

#### 3. Display an A-Z Indexed List of Brands

**Shortcode:** `[brands_list]`

**Description:** Displays an A-Z indexed list of brands with an optional option to show brand images.

**Attributes:**

- `show_images` (bool): Show or hide brand images next to brand names. Default is `false`.
- `show_title` (bool): Show or hide brand names. Default is `true`.

**Usage Examples:**

- **Default List (brand images hidden):**
```
[brands_list]
```

- **List with Brand Images:**
```
[brands_list show_images="true"]
```

- **List with Brand Images and Titles Hidden:**
```
[brands_list show_images="true" show_title="false"]
```

**Example Output:**

An A-Z navigation menu at the top. Clicking on a letter scrolls to the respective section of brands starting with that letter. Brands are listed with or without images based on the `show_images` attribute.

### Enqueueing Styles

The plugin automatically enqueues its styles. However, if you wish to customize the styles further, you can override the CSS by adding your own styles in your theme or child theme.

**Default Styles:**

Located in `assets/css/brands-for-woocommerce.css`.

**Example Custom CSS:**
```
.brand-index-menu {
    margin-bottom: 20px;
    text-align: center;
}
.brand-index-menu a {
    margin: 0 5px;
    font-size: 16px;
    font-weight: bold;
    color: #0073aa;
    text-decoration: none;
}
.brand-index-menu a:hover {
    color: #005177;
}
.brands-list {
    margin-top: 20px;
}
.brands-list h2 {
    font-size: 20px;
    border-bottom: 1px solid #ddd;
    padding-bottom: 5px;
    margin-top: 30px;
}
.brands-list ul {
    list-style: none;
    padding-left: 0;
}
.brands-list ul li {
    padding: 5px 0;
}
.brands-list ul li a {
    color: #0073aa;
    text-decoration: none;
}
.brands-list ul li a:hover {
    color: #005177;
}
.brand-image {
    max-width: 50px;
    height: auto;
    margin-right: 10px;
}
```

## Widgets

### Brand Thumbnails Widget

**Description:** Displays brand thumbnails with options to show/hide brand names and descriptions, limit the number of displayed brands, and randomize the display order.

**How to Add the Widget:**

1. Navigate to **Appearance > Widgets** in your WordPress admin dashboard.
2. Locate the **Brand Thumbnails Widget**.
3. Drag and drop the widget into your desired widget area (e.g., Sidebar, Footer).
4. Configure the widget settings:
    - **Show Brand Name:** Toggle the display of brand names.
    - **Show Brand Description:** Toggle the display of brand descriptions.
    - **Number of Brands to Display:** Set the limit for the number of brands shown.
    - **Randomize Brands Display Order:** Shuffle the order of displayed brands.
5. Click **Save** to apply the settings.

**Widget Settings Explained:**

- **Show Brand Name:** When checked, the brand name will be displayed below the brand image.
- **Show Brand Description:** When checked, the brand description will be displayed below the brand name.
- **Number of Brands to Display:** Defines how many brands will be shown in the widget. Defaults to `5`.
- **Randomize Brands Display Order:** When enabled, brands will be displayed in a random order each time the page loads.

**Example Widget Output:**

A grid of brand thumbnails with optional names and descriptions, adhering to the configured settings.

## REST API

The plugin registers a REST API endpoint to fetch all brands.

### Endpoint
```
GET /wp-json/wc/v3/brands
```

### Response

Returns a JSON array of brands with the following fields:

- `id`: Brand term ID.
- `name`: Brand name.
- `description`: Brand description.
- `slug`: Brand slug.
- `count`: Number of products associated with the brand.
- `image`: URL to the brand image (if available).
- `url`: URL to the brand archive page.

### Example Request
```
curl -X GET https://yourwebsite.com/wp-json/wc/v3/brands
```

### Example Response
```
[
    {
        "id": 1,
        "name": "Brand A",
        "description": "Description for Brand A.",
        "slug": "brand-a",
        "count": 10,
        "image": "https://yourwebsite.com/images/brand-a.jpg",
        "url": "https://yourwebsite.com/brand/brand-a/"
    },
    {
        "id": 2,
        "name": "Brand B",
        "description": "Description for Brand B.",
        "slug": "brand-b",
        "count": 8,
        "image": "https://yourwebsite.com/images/brand-b.jpg",
        "url": "https://yourwebsite.com/brand/brand-b/"
    }
    // ...additional brands
]
```

## Customization

### Overriding Templates

The plugin uses WooCommerce's template system to display products. If you wish to customize the display, you can override WooCommerce templates in your theme.

1. **Copy Template File:**

Locate the template file you want to override (e.g., `content-product.php`).

2. **Paste into Theme:**

Copy the file to your theme's WooCommerce directory: `your-theme/woocommerce/content-product.php`.

3. **Modify as Needed:**

Edit the copied template file to customize the product display.

### Adding Custom Styles

To further customize the appearance of brand listings and widgets, add custom CSS to your theme or child theme.

**Example:**
```
.product-brand-grid {
    display: grid;
    gap: 20px;
    justify-items: center;
}

.product-brand-item {
    text-align: center;
}

.brand-image {
    max-width: 100px;
    height: auto;
}

.brand-name {
    font-size: 16px;
    font-weight: bold;
    margin-top: 10px;
}

.brand-description {
    font-size: 14px;
    color: #666;
    margin-top: 5px;
}
```

## Frequently Asked Questions

**Q1: How do I add a brand image?**  
**A:** When adding or editing a brand, use the **Upload Image** button to select or upload an image from your media library. The image will be displayed alongside the brand name.

**Q2: Can I disable brand descriptions globally?**  
**A:** Yes. By default, brand descriptions are hidden in the grid layout. You can enable them using the `show_description` attribute in the `[product_brand]` shortcode or within the widget settings.

**Q3: How do I update the plugin?**  
**A:** The plugin uses a GitHub-based update checker. Ensure that the `Update URI` in the plugin header points to your repository. Updates will be handled automatically through the WordPress dashboard.

**Q4: Can I change the taxonomy slug from `brand` to something else?**  
**A:** Currently, the taxonomy slug is set to `brand`. To change it, modify the `rewrite` argument in the `register_brand_taxonomy` method and ensure that permalinks are refreshed by visiting **Settings > Permalinks** and clicking **Save Changes**.

**Q5: Is the plugin compatible with all WooCommerce themes?**  
**A:** The plugin is designed to integrate seamlessly with most WooCommerce-compatible themes. However, some themes may require additional CSS adjustments for optimal display.

## Support

For support, please visit the [GitHub Issues](https://github.com/robertdevore/brands-for-woocommerce/issues) page of the repository. Feel free to report bugs, request features, or ask questions.