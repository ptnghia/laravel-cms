<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Form>
 */
class FormFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $formTypes = [
            [
                'name' => 'contact_form',
                'title' => 'Contact Form',
                'description' => 'General contact form for inquiries',
                'fields' => [
                    ['name' => 'name', 'type' => 'text', 'label' => 'Full Name', 'required' => true],
                    ['name' => 'email', 'type' => 'email', 'label' => 'Email Address', 'required' => true],
                    ['name' => 'subject', 'type' => 'text', 'label' => 'Subject', 'required' => true],
                    ['name' => 'message', 'type' => 'textarea', 'label' => 'Message', 'required' => true],
                ],
            ],
            [
                'name' => 'newsletter_signup',
                'title' => 'Newsletter Signup',
                'description' => 'Subscribe to our newsletter',
                'fields' => [
                    ['name' => 'email', 'type' => 'email', 'label' => 'Email Address', 'required' => true],
                    ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'required' => false],
                ],
            ],
            [
                'name' => 'feedback_form',
                'title' => 'Feedback Form',
                'description' => 'Share your feedback with us',
                'fields' => [
                    ['name' => 'rating', 'type' => 'select', 'label' => 'Rating', 'options' => ['1', '2', '3', '4', '5']],
                    ['name' => 'feedback', 'type' => 'textarea', 'label' => 'Your Feedback', 'required' => true],
                    ['name' => 'recommend', 'type' => 'radio', 'label' => 'Would you recommend us?', 'options' => ['Yes', 'No']],
                ],
            ],
        ];

        $formType = fake()->randomElement($formTypes);

        return [
            'name' => $formType['name'],
            'title' => $formType['title'],
            'description' => $formType['description'],
            'form_fields' => $formType['fields'],
            'settings' => fake()->optional(0.6)->randomElement([
                [
                    'send_email' => true,
                    'email_to' => fake()->safeEmail(),
                    'success_message' => 'Thank you for your submission!',
                    'redirect_url' => fake()->optional(0.3)->url(),
                    'store_submissions' => true,
                    'require_login' => fake()->boolean(20),
                ],
                null,
            ]),
            'is_active' => fake()->boolean(85), // 85% chance of being active
            'user_id' => \App\Models\User::factory(),
        ];
    }

    /**
     * Create an active form.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create a contact form.
     */
    public function contact(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'contact_form',
            'title' => 'Contact Form',
            'description' => 'Get in touch with us',
            'form_fields' => [
                ['name' => 'name', 'type' => 'text', 'label' => 'Full Name', 'required' => true],
                ['name' => 'email', 'type' => 'email', 'label' => 'Email Address', 'required' => true],
                ['name' => 'phone', 'type' => 'tel', 'label' => 'Phone Number', 'required' => false],
                ['name' => 'subject', 'type' => 'text', 'label' => 'Subject', 'required' => true],
                ['name' => 'message', 'type' => 'textarea', 'label' => 'Message', 'required' => true],
            ],
        ]);
    }

    /**
     * Create a newsletter form.
     */
    public function newsletter(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'newsletter_signup',
            'title' => 'Newsletter Signup',
            'description' => 'Subscribe to our newsletter for updates',
            'form_fields' => [
                ['name' => 'email', 'type' => 'email', 'label' => 'Email Address', 'required' => true],
                ['name' => 'name', 'type' => 'text', 'label' => 'First Name', 'required' => false],
            ],
        ]);
    }

    /**
     * Create a survey form.
     */
    public function survey(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'customer_survey',
            'title' => 'Customer Survey',
            'description' => 'Help us improve our services',
            'form_fields' => [
                ['name' => 'satisfaction', 'type' => 'select', 'label' => 'Satisfaction Level', 'options' => ['Very Satisfied', 'Satisfied', 'Neutral', 'Dissatisfied', 'Very Dissatisfied']],
                ['name' => 'comments', 'type' => 'textarea', 'label' => 'Additional Comments', 'required' => false],
                ['name' => 'recommend', 'type' => 'radio', 'label' => 'Would you recommend us?', 'options' => ['Yes', 'No', 'Maybe']],
            ],
        ]);
    }
}
