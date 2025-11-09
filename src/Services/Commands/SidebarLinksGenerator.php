<?php

namespace QuickerFaster\LaravelUI\Services\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\File;

class SidebarLinksGenerator extends Command
{


    public function generateSidebarLinks($module, $modelName, $modelData, $command)
    {
        $sidebar = $modelData['sidebar'] ?? [];

        if (isset($sidebar['add']) && !$sidebar['add']) {
            return;
        }

        $sidebarConfigPath = base_path("app/Modules/{$module}/Config/sidebar_menu.php");

        $newEntry = $this->getSidebarEntryArray($module, $modelName, $modelData);

        $existing = [];

        if (File::exists($sidebarConfigPath)) {
            $existing = include $sidebarConfigPath;
        }

        // Avoid duplicate entries by checking title and url
        $isDuplicate = collect($existing)->contains(function ($entry) use ($newEntry) {
            return $entry['title'] === $newEntry['title'] && $entry['url'] === $newEntry['url'];
        });

        if (!$isDuplicate) {
            if (isset($newEntry['groupTitle']))  {
                // Prepend with the title entry
                $existing[] = [
                    'itemType' => 'item-separator',
                    'title' => '<h6 class="ps-3 mt-4 mb-2 text-uppercase text-xs font-weight-bolder opacity-6 group-title">'.$newEntry['groupTitle'].'</h6>',
                    'url' => null,
                ];
            }

            $existing[] = $newEntry;

            $export = var_export($existing, true);
            File::ensureDirectoryExists(dirname($sidebarConfigPath));
            File::put($sidebarConfigPath, "<?php\n\nreturn {$export};\n");

            $command->info("Sidebar menu updated: {$sidebarConfigPath}");
        } else {
            $command->info("Sidebar entry already exists. Skipping: {$sidebarConfigPath}");
        }
    }

    protected function getSidebarEntryArray($module, $modelName, $modelData)
    {
        $sidebar = $modelData['sidebar'] ?? [];

        $icon = $sidebar['iconClasses'] ?? $modelData['iconClasses'] ?? 'fas fa-cube';
        $url = $sidebar['url'] ?? Str::kebab(Str::plural($modelName));
        $title = $sidebar['title'] ?? Str::title(Str::snake(Str::plural($modelName), ' '));

        $entry = [
            'title' => $title,
            'icon'  => $icon,
            'url'   => "{$module}/{$url}",
        ];
        // Add groupTitle if it exist
        if (isset($sidebar['groupTitle']))
            $entry["groupTitle"] = $sidebar['groupTitle'];

        if (!empty($sidebar['submenu']) && is_array($sidebar['submenu'])) {
            $entry['submenu'] = collect($sidebar['submenu'])->map(function ($sub) use ($module) {
                return [
                    'title' => $sub['title'] ?? 'Subitem',
                    'url'   => "{$module}/" . ltrim($sub['url'] ?? '', '/'),
                ];
            })->toArray();
        }

        return $entry;
    }


    protected function getSidebarLinksStub($module, $modelName, $modelData)
    {
        $sidebar = $modelData['sidebar']?? [];
        $iconClasses = $sidebar["iconClasses"]?? $modelData['iconClasses']?? "fas fa-user";

        $url = Str::plural(str_replace("_", "-", Str::snake($modelName)));
        $title = Str::plural(str_replace("_", " ", Str::snake($modelName)));
        $title = $sidebar['title']?? "Manage ".$title;
        $url = $sidebar['url']?? $url;
        //$iconClasses = $sidebar['iconClasses']?? "fas fa-cubes sidebar-icon";

        return "<system.views::layouts.navbars.sidebar-link-item\n" .
            "    iconClasses=\"$iconClasses sidebar-icon\"\n" .
            "    url=\"{$module}/" . strtolower($url) . "\"\n" . // Default URL: /{module}/{pluralized_module_name}
            "    title=\"" . Str::title(Str::plural($title)) . "\"\n" . // Default title
            "/>\n";
    }





}
