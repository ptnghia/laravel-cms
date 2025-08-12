<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'subject',
        'content',
    ];

    /**
     * Get template by name.
     */
    public static function getByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * Render the template with variables.
     */
    public function render(array $variables = []): array
    {
        $subject = $this->renderString($this->subject, $variables);
        $content = $this->renderString($this->content, $variables);

        return [
            'subject' => $subject,
            'content' => $content,
        ];
    }

    /**
     * Render a string with variables.
     */
    protected function renderString(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $template = str_replace($placeholder, $value, $template);
        }

        return $template;
    }

    /**
     * Get all available variables from the template.
     */
    public function getVariablesAttribute(): array
    {
        $variables = [];
        $content = $this->subject . ' ' . $this->content;

        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);

        if (!empty($matches[1])) {
            $variables = array_unique($matches[1]);
        }

        return $variables;
    }

    /**
     * Check if the template has a specific variable.
     */
    public function hasVariable(string $variable): bool
    {
        return in_array($variable, $this->variables);
    }

    /**
     * Validate that all required variables are provided.
     */
    public function validateVariables(array $variables): array
    {
        $missing = [];

        foreach ($this->variables as $required) {
            if (!array_key_exists($required, $variables)) {
                $missing[] = $required;
            }
        }

        return $missing;
    }
}
