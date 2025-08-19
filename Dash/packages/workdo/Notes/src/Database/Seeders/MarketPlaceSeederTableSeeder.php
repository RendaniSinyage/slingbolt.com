<?php

namespace Workdo\Notes\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Workdo\LandingPage\Entities\MarketplacePageSetting;


class MarketPlaceSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $module = 'Notes';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Notes';
        $data['product_main_description'] = '<p>Welcome to Diary and Notes Keeper, the perfect app to help you keep track of your thoughts, memories, and to-do lists in one convenient place! we have provide personal and shared two type note.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = 'Notes Management';
        $data['dedicated_theme_description'] = '<p>Diary and Notes Keeper is an app that helps users keep track of your thoughts, memories, and to-do lists in one convenient place.</p>';
        $data['dedicated_theme_sections'] = '[{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"What are the benefits of using Notes? ","dedicated_theme_section_description":"<p>One of the benefits of using a Daily Diary App is that it can help users develop a consistent writing habit, which can improve their mental health and wellbeing. By recording their experiences and emotions regularly, users can gain a better understanding of themselves, identify patterns in their behavior, and track their progress towards personal goals.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null},"2":{"title":"null","description":null},"3":{"title":null,"description":null}}},{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"What is the use of Notes?","dedicated_theme_section_description":" <p>The easiest way to start using the Apple Pencil to take notes on your iPad is to open the Apple Notes app, tap the New Note/Compose button in the top-right corner and start writing or drawing. You don`t have to select the Apple Pencil icon before you start using it. That icon only brings up additional tools for you to change the type of tool the Apple Pencil is set to, like an eraser or a marker, along with ink color.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null},"2":{"title":null,"description":null},"3":{"title":null,"description":null}}}]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"Notes"},{"screenshots":"","screenshots_heading":"Notes"},{"screenshots":"","screenshots_heading":"Notes"},{"screenshots":"","screenshots_heading":"Notes"},{"screenshots":"","screenshots_heading":"Notes"}]';
        $data['addon_heading'] = 'Why choose dedicated modules for Your Business?';
        $data['addon_description'] = '<p>With Dash, you can conveniently manage all your business functions from a single location.</p>';
        $data['addon_section_status'] = 'on';
        $data['whychoose_heading'] = 'Why choose dedicated modules for Your Business?';
        $data['whychoose_description'] = '<p>With Dash, you can conveniently manage all your business functions from a single location.</p>';
        $data['pricing_plan_heading'] = 'Empower Your Workforce with DASH';
        $data['pricing_plan_description'] = '<p>Access over Premium Add-ons for Accounting, HR, Payments, Leads, Communication, Management, and more, all in one place!</p>';
        $data['pricing_plan_demo_link'] = '#';
        $data['pricing_plan_demo_button_text'] = 'View Live Demo';
        $data['pricing_plan_text'] = '{"1":{"title":"Pay-as-you-go"},"2":{"title":"Unlimited installation"},"3":{"title":"Secure cloud storage"}}';
        $data['whychoose_sections_status'] = 'on';
        $data['dedicated_theme_section_status'] = 'on';

        foreach($data as $key => $value){
            if(!MarketplacePageSetting::where('name', '=', $key)->where('module', '=', $module)->exists()){
                MarketplacePageSetting::updateOrCreate(
                [
                    'name' => $key,
                    'module' => $module

                ],
                [
                    'value' => $value
                ]);
            }
        }
    }
}
