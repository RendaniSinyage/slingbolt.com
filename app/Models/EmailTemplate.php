<?php
// app/Models/EmailTemplate.php (Updated to match your existing structure)

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'from',
        'slug',
        'created_by',
          'is_enabled',
    ];

    public function template()
    {
        return $this->hasOne('App\Models\UserEmailTemplate', 'template_id', 'id')->where('user_id', '=', \Auth::user()->id);
    }

    // Relationship with email_template_langs
    public function templateLangs()
    {
        return $this->hasMany('App\Models\EmailTemplateLang', 'parent_id', 'id');
    }

    // Get template content for specific language
    public function getTemplateLang($lang = 'en')
    {
        return $this->templateLangs()->where('lang', $lang)->first();
    }

    private static $templateData = NULL;

    public static function emailTemplateData()
    {
        if(self::$templateData == null)
        {
            $emailTemplate = EmailTemplate::first();
            self::$templateData = $emailTemplate;
        }
        return self::$templateData;
    }
}
