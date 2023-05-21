<?php

namespace ElementContextYT;

if (!defined('ABSPATH')) exit;

class ContextTransform
{
    public function __invoke($node)
    {
        $props = $node->props;
        if (!isset($props['element_context_yt'])) {
            return;
        }

        $element_context_yt = $props['element_context_yt'];
        if ($element_context_yt->context === 'all') {
            return true;
        }
        if ($element_context_yt->context === 'none') {
            return false;
        }

        $languageMatched = !empty($element_context_yt->match_language) 
            ? $this->matchLanguage($element_context_yt->match_language) :
             true;
        $locationMatched = !empty($element_context_yt->match_location)
            ? $this->matchLocations($element_context_yt->match_location) 
            : false;

        $archivePostMatched = !empty($element_context_yt->match_archive_postype)
            ? $this->matchArchivePost($element_context_yt->match_archive_postype) 
            : false;
        $singlePostMatched = !empty($element_context_yt->match_single_postype)
            ? $this->matchSinglePost($element_context_yt->match_single_postype) 
            : false;
        $archiveTaxMatched = !empty($element_context_yt->match_archive_taxonomy)
            ? $this->matchArchiveTax($element_context_yt->match_archive_taxonomy) 
            : false;
        $urlMatched = !empty($element_context_yt->match_url)
            ? $this->matchUrl($element_context_yt->match_url) 
            : false;

        $isMatched = $languageMatched && ($locationMatched || $archivePostMatched || $singlePostMatched || $archiveTaxMatched || $urlMatched);
        if ($element_context_yt->context === 'show_selected') {
            return $isMatched;
        }

        if ($element_context_yt->context === 'hide_selected') {
            return !$isMatched;
        }
    }
    private function matchLanguage($language)
    {
        $match_string = str_replace(' ', '', $language);
        $match_array = explode(',', $match_string);

        foreach ($match_array as $language) {
            if (get_locale() === $language)
                return true;
        }

        return false;
    }
    private function matchLocations($locations)
    {
        foreach ($locations as $location) {
            if ($this->checkLocation($location))
                return true;
        }

        return false;
    }
    private function checkLocation($location)
    {
        switch ($location) {
            case 'is_front_page':
                return is_front_page();
            case 'is_home':
                return is_home();
            case 'is_singular':
                return is_singular();
            case 'is_single':
                return is_singular('post');
            case 'is_page':
                return (is_page() && !is_front_page());
            case 'is_attachment':
                return is_attachment();
            case 'is_search':
                return is_search();
            case 'is_404':
                return is_404();
            case 'is_archive':
                return is_archive();
            case 'is_date':
                return is_date();
            case 'is_day':
                return is_day();
            case 'is_month':
                return is_month();
            case 'is_year':
                return is_year();
            case 'is_category':
                return is_category();
            case 'is_tag':
                return is_tag();
            case 'is_author':
                return is_author();
        }
    }
    private function matchArchivePost($posts)
    {
        $match_string = str_replace(' ', '', $posts);
        $match_array = explode(',', $match_string);

        foreach ($match_array as $post) {
            if (is_post_type_archive($post))
                return true;
        }

        return false;
    }
    private function matchSinglePost($posts)
    {
        $match_string = str_replace(' ', '', $posts);
        $match_array = explode(',', $match_string);

        foreach ($match_array as $post) {
            if (is_singular($post))
                return true;
        }

        return false;
    }
    private function matchArchiveTax($taxonomies)
    {
        $match_string = str_replace(' ', '', $taxonomies);
        $match_array = explode(',', $match_string);

        foreach ($match_array as $tax) {
            if (is_tax($tax))
                return true;
        }

        return false;
    }
    private function matchUrl($urls)
    {
        $path = $this->get_request_path();
        if ($this->match_path($path, $urls)) {
            return true;
        }

        return false;
    }
    private function has_rules_with_query_strings($rules)
    {
        foreach ($rules as $rule) {
            if (false !== strpos($rule, '=')) {
                return true;
            }
        }
        return false;
    }
    private function match_path($path, $rules)
    {
        $uri_rules_paths = array_map('trim', $this->uri_rules_from_paths($rules));
        if (!$this->has_rules_with_query_strings($uri_rules_paths)) {
            $path = strtok($path, '?');
        }

        if (!empty($uri_rules_paths)) {
            return $this->uri_matches_rules($path, $uri_rules_paths);
        }

        return false;
    }
    private function uri_rules_from_paths($paths)
    {
        $patterns = explode(",", $paths);

        $patterns = array_map(
            function ($pattern) {
                return $this->path_from_uri(trim($pattern));
            },
            $patterns
        );

        return array_filter($patterns);
    }
    private function get_request_path()
    {
        static $path;
        if (!isset($path)) {
            $path = $this->path_from_uri($_SERVER['REQUEST_URI']);
        }
        return $path;
    }
    private function path_from_uri($uri)
    {
        $parts = wp_parse_args(
            wp_parse_url($uri),
            array(
                'path' => '',
            )
        );

        $path = trim($parts['path'], '/');

        if (!empty($parts['query'])) {
            $path .= '?' . $parts['query'];
        }

        return $path;
    }
    private function quote_rules($rules)
    {
        return array_map(
            function ($rule) {
                $rule = preg_quote($rule, '/');
                return str_replace(array_keys(['\*' => '.*']), ['\*' => '.*'], $rule);
            },
            $rules
        );
    }
    private function rules_to_expression($rules)
    {
        $rules = array_map(
            function ($rule) {
                return sprintf('(%s$)', $rule);
            },
            $this->quote_rules($rules)
        );

        return sprintf('%s^(%s)%si', '/', implode('|', $rules), '/');
    }
    private function uri_matches_rules($uri, $rules)
    {
        if (!empty($rules)) {
            return (bool) preg_match($this->rules_to_expression($rules), $uri);
        }
        return false;
    }
}
