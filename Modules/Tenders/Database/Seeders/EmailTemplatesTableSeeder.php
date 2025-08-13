<?php

namespace Modules\Tenders\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;

class EmailTemplatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $template = EmailTemplate::create([
            'name' => 'new_tender',
            'from' => 'Tender Assistant Officer',
            'slug' => 'new_tender',
            'created_by' => 1, // Assuming super admin has id 1
        ]);

        EmailTemplateLang::create([
            'parent_id' => $template->id,
            'lang' => 'en',
            'subject' => 'New Tenders Found',
            'content' => '<p>Hello {user_name},</p><p>I have found new tenders that match your criteria. Please log in to review them.</p><p>{app_url}</p><p>Thank you,<br>Tender Assistant Officer</p>',
        ]);
    }
}
