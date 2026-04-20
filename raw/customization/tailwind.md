# Tailwind

> Include plugin views in your panel's theme.css source list.

The plugin's Blade views use Tailwind utilities. If your panel has a compiled theme, include the plugin's views in its source list:

```css [resources/css/filament/{panel}/theme.css][resources/css/filament//theme.css]
@source '../../../../vendor/relaticle/activity-log/resources/views/**/*';
```
