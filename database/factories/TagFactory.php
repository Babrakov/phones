<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
//use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class TagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tag::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'         => $this->faker->unique()->word,
            'desc'         => $this->faker->text(50),
            'published_at' => Carbon::now()->subDays(rand(0, 30)),
        ];
    }
}
