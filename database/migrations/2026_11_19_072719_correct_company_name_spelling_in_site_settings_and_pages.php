<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->replaceLegacyName('Cleaniners', 'Cleaners');
        $this->replaceLegacyName('CLEANINERS', 'CLEANERS');
    }

    public function down(): void
    {
        $this->replaceLegacyName('Cleaners', 'Cleaniners');
        $this->replaceLegacyName('CLEANERS', 'CLEANINERS');
    }

    private function replaceLegacyName(string $old, string $new): void
    {
        foreach (['company_name', 'seo_title', 'footer_description'] as $key) {
            $settings = DB::table('site_settings')->where('key', $key)->where('value', 'like', '%'.$old.'%')->get(['id', 'value']);
            foreach ($settings as $setting) {
                DB::table('site_settings')->where('id', $setting->id)->update(['value' => str_replace($old, $new, $setting->value)]);
            }
        }

        $homepage = DB::table('site_settings')->where('key', 'homepage_content')->first(['id', 'value']);
        if ($homepage && is_string($homepage->value) && str_contains($homepage->value, $old)) {
            DB::table('site_settings')->where('id', $homepage->id)->update(['value' => str_replace($old, $new, $homepage->value)]);
        }

        foreach (['title', 'summary', 'body', 'seo_title', 'seo_description'] as $column) {
            $pages = DB::table('content_pages')->where($column, 'like', '%'.$old.'%')->get(['id', $column]);
            foreach ($pages as $page) {
                DB::table('content_pages')->where('id', $page->id)->update([$column => str_replace($old, $new, $page->{$column})]);
            }
        }
    }
};
