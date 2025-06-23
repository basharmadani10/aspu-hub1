<?php

// app/Helpers/GeneralHelper.php

// This ensures the function is only declared once, preventing "Cannot redeclare" errors.
if (!function_exists('active_class')) {
    /**
     * Generates CSS classes for active/inactive navigation links based on current route.
     *
     * @param string|array $routeNames The route name(s) to check against. Can be a string or an array of strings.
     * @return string CSS classes for the link.
     */
    function active_class($routeNames)
    {
        $routes = is_array($routeNames) ? $routeNames : [$routeNames];
        $baseClasses = 'flex items-center px-4 py-3 transition duration-200 rounded-lg';

        foreach ($routes as $routeName) {
            // Checks if the current route matches any of the provided route names (supports wildcards like 'admin.users.*')
            if (request()->routeIs($routeName)) {
                return "$baseClasses bg-indigo-600 text-white"; // Active classes
            }
        }

        return "$baseClasses text-gray-600 hover:bg-indigo-50 hover:text-indigo-600"; // Inactive classes
    }
}

// You can add other global helper functions here in the future if needed.
