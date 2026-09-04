<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            // Part 1 - Introduction & Interview
            [
                'part' => 1,
                'topic' => 'Hometown',
                'prompt' => 'Can you describe the town or city where you grew up?',
            ],
            [
                'part' => 1,
                'topic' => 'Hobbies',
                'prompt' => 'What do you usually do in your free time?',
            ],
            [
                'part' => 1,
                'topic' => 'Work or Study',
                'prompt' => 'Do you work or are you a student? Why did you choose that field?',
            ],

            // Part 2 - Long Turn (cue card)
            [
                'part' => 2,
                'topic' => 'A Memorable Trip',
                'prompt' => 'Describe a trip you really enjoyed. You should say: where you went, who you went with, what you did there, and explain why you enjoyed it so much.',
            ],
            [
                'part' => 2,
                'topic' => 'A Skill You Want to Learn',
                'prompt' => 'Describe a skill you would like to learn. You should say: what it is, why you want to learn it, how you would learn it, and explain how it would help you in the future.',
            ],
            [
                'part' => 2,
                'topic' => 'A Piece of Technology',
                'prompt' => 'Describe a piece of technology you use often. You should say: what it is, how often you use it, what you use it for, and explain why it is important to you.',
            ],

            // Part 3 - Two-way discussion
            [
                'part' => 3,
                'topic' => 'Travel & Culture',
                'prompt' => 'Do you think traveling abroad changes the way people see their own culture? Why or why not?',
            ],
            [
                'part' => 3,
                'topic' => 'Technology & Society',
                'prompt' => 'How has technology changed the way people communicate with each other in your country?',
            ],
            [
                'part' => 3,
                'topic' => 'Education',
                'prompt' => 'What are the advantages and disadvantages of online learning compared to traditional classrooms?',
            ],
        ];

        // Keyed on the natural key so re-seeding cannot duplicate the catalogue.
        foreach ($questions as $question) {
            Question::query()->firstOrCreate($question);
        }
    }
}
