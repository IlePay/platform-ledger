<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['key', 'name', 'subject', 'body', 'variables', 'is_active'];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    // Rendre le template avec des variables
    public function render($data = []): string
    {
        $body = $this->body;
        
        foreach ($data as $key => $value) {
            $body = str_replace("{{ $key }}", $value, $body);
        }
        
        return $body;
    }

    // Helper statique pour récupérer et rendre un template
public static function renderTemplate(string $key, array $data = []): ?string
{
    $template = self::where('key', $key)
        ->where('is_active', true)
        ->first();
    
    if (!$template) {
        return null;
    }
    
    return $template->render($data);
}

    public static function getSubject(string $key, array $data = []): ?string
    {
        $template = self::where('key', $key)
            ->where('is_active', true)
            ->first();
        
        if (!$template) {
            return null;
        }
        
        $subject = $template->subject;
        
        foreach ($data as $variable => $value) {
            $subject = str_replace("{{ $variable }}", $value, $subject);
        }
        
        return $subject;
    }
}