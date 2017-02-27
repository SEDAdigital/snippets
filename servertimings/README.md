# Server-Timings

## Purpose
Plugin which sends timing data to the Chrome Developer Tools, where the data is visible in the Network/Timings tab.

## Use
Create a plugin, copy the code from the file `servertimings.plugin.php` into it. Activate the following system events on the plugin:
```
OnWebPageInit
OnInitCulture
OnLoadWebDocument
OnWebPagePrerender
```

Server timings are only sent to the browser if the user is logged into the backend.

## What you will see
