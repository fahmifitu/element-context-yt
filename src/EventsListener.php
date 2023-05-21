<?php

namespace ElementContextYT;

if (!defined('ABSPATH')) exit;

use YOOtheme\Config;
use YOOtheme\Path;

class EventsListener
{

    public static function onCustomizerInit(Config $config) {
        if (apply_filters('element_context_yt_disable', false)) {
            return;
        }
        $config->addFile('customizer', Path::get('./panel.json'));
    }
    
    public static function onBuilderType($data) {
        if (apply_filters('element_context_yt_disable', false, $data)) {
            return $data;
        }
        
        if ( 
            isset($data['fieldset']) && 
            isset($data['fieldset']['default']) && 
            isset($data['fieldset']['default']['fields'][2])
        ) {
            if ($data['fieldset']['default']['fields'][2]['title'] === 'Advanced') {
                $data['fieldset']['default']['fields'][2]['fields'][] = 'element_context_yt';
                $data['fields']['element_context_yt'] = [
                    "label" => "Element Context",
                    "type" => "button-panel",
                    "text" => "Settings",
                    "panel" => "element_context_yt",
                ];
            }
        }
        
        return $data;
    }
}
