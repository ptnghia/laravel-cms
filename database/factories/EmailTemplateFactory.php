<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailTemplate>
 */
class EmailTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $templates = [
            [
                'name' => 'welcome_email',
                'subject' => 'Welcome to {{site_name}}!',
                'content' => 'Hello {{user_name}},\n\nWelcome to {{site_name}}! We\'re excited to have you on board.\n\nBest regards,\nThe {{site_name}} Team',
            ],
            [
                'name' => 'password_reset',
                'subject' => 'Reset Your Password - {{site_name}}',
                'content' => 'Hello {{user_name}},\n\nYou requested a password reset. Click the link below to reset your password:\n\n{{reset_link}}\n\nIf you didn\'t request this, please ignore this email.\n\nBest regards,\nThe {{site_name}} Team',
            ],
            [
                'name' => 'new_comment',
                'subject' => 'New Comment on "{{post_title}}"',
                'content' => 'Hello {{author_name}},\n\nA new comment has been posted on your article "{{post_title}}".\n\nComment by: {{commenter_name}}\nComment: {{comment_content}}\n\nView the comment: {{post_url}}\n\nBest regards,\nThe {{site_name}} Team',
            ],
            [
                'name' => 'order_confirmation',
                'subject' => 'Order Confirmation - {{order_number}}',
                'content' => 'Hello {{customer_name}},\n\nThank you for your order! Here are the details:\n\nOrder Number: {{order_number}}\nTotal Amount: {{total_amount}}\n\nWe\'ll send you another email when your order ships.\n\nBest regards,\nThe {{site_name}} Team',
            ],
            [
                'name' => 'newsletter',
                'subject' => '{{newsletter_title}} - {{site_name}}',
                'content' => 'Hello {{subscriber_name}},\n\n{{newsletter_content}}\n\nTo unsubscribe, click here: {{unsubscribe_link}}\n\nBest regards,\nThe {{site_name}} Team',
            ],
        ];

        $template = fake()->randomElement($templates);

        return [
            'name' => $template['name'],
            'subject' => $template['subject'],
            'content' => $template['content'],
        ];
    }

    /**
     * Create a welcome email template.
     */
    public function welcome(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'welcome_email',
            'subject' => 'Welcome to {{site_name}}!',
            'content' => 'Hello {{user_name}},\n\nWelcome to {{site_name}}! We\'re excited to have you on board.\n\nYour account has been successfully created. You can now log in and start exploring our features.\n\nIf you have any questions, feel free to contact our support team.\n\nBest regards,\nThe {{site_name}} Team',
        ]);
    }

    /**
     * Create a password reset template.
     */
    public function passwordReset(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'password_reset',
            'subject' => 'Reset Your Password - {{site_name}}',
            'content' => 'Hello {{user_name}},\n\nYou requested a password reset for your account. Click the link below to reset your password:\n\n{{reset_link}}\n\nThis link will expire in 60 minutes for security reasons.\n\nIf you didn\'t request this password reset, please ignore this email.\n\nBest regards,\nThe {{site_name}} Team',
        ]);
    }

    /**
     * Create an order confirmation template.
     */
    public function orderConfirmation(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'order_confirmation',
            'subject' => 'Order Confirmation - {{order_number}}',
            'content' => 'Hello {{customer_name}},\n\nThank you for your order! Here are the details:\n\nOrder Number: {{order_number}}\nOrder Date: {{order_date}}\nTotal Amount: {{total_amount}}\n\nShipping Address:\n{{shipping_address}}\n\nWe\'ll send you another email when your order ships.\n\nBest regards,\nThe {{site_name}} Team',
        ]);
    }

    /**
     * Create a comment notification template.
     */
    public function commentNotification(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'new_comment',
            'subject' => 'New Comment on "{{post_title}}"',
            'content' => 'Hello {{author_name}},\n\nA new comment has been posted on your article "{{post_title}}".\n\nComment by: {{commenter_name}}\nComment: {{comment_content}}\n\nYou can view and moderate the comment here: {{post_url}}\n\nBest regards,\nThe {{site_name}} Team',
        ]);
    }
}
