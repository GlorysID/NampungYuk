<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'project_type' => fake()->randomElement(['web', 'mobile', 'cli', 'library']),
            'status' => fake()->randomElement(['idea', 'prototype', 'beta', 'production']),
            'title' => ucwords($title),
            'slug' => Str::slug($title),
            'tagline' => fake()->sentence(8),
            'description' => fake()->paragraphs(2, true),
            'challenges' => fake()->paragraph(),
            'learnings' => fake()->paragraph(),
            'thumbnail' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80',
            'demo_url' => fake()->boolean(70) ? fake()->url() : null,
            'github_url' => fake()->boolean(80) ? 'https://github.com/'.fake()->userName().'/'.Str::slug($title) : null,
            'prototype_url' => null,
            'tech_stacks' => fake()->randomElements(['Laravel', 'Vue.js', 'TailwindCSS', 'PostgreSQL', 'TypeScript', 'Docker', 'Alpine.js'], 3),
            'setup_instructions' => "git clone https://github.com/demo/repo.git\ncd repo\ncomposer install\nphp artisan migrate",
            'upvotes_count' => fake()->numberBetween(0, 50),
            'downvotes_count' => fake()->numberBetween(0, 5),
            'score' => fake()->numberBetween(0, 50),
            'comments_count' => fake()->numberBetween(0, 10),
            'views_count' => fake()->numberBetween(10, 500),
            'is_featured' => false,
        ];
    }
}
