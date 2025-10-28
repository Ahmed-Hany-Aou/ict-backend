<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Slide;
use App\Models\Quiz;

class FixSlideDataStructure extends Command
{
    protected $signature = 'fix:slide-data';
    protected $description = 'Fix slide and quiz data structure to match frontend expectations';

    public function handle()
    {
        $this->info('Fixing slide data structure...');

        // Fix Slides
        $slides = Slide::all();
        $slideCount = 0;

        foreach ($slides as $slide) {
            $modified = false;
            $content = $slide->content;

            if (is_array($content)) {
                // Fix points: [{text: "point"}] -> ["point"]
                if (isset($content['points']) && is_array($content['points'])) {
                    $newPoints = [];
                    foreach ($content['points'] as $point) {
                        if (is_array($point) && isset($point['text'])) {
                            $newPoints[] = $point['text'];
                            $modified = true;
                        } elseif (is_string($point)) {
                            $newPoints[] = $point;
                        }
                    }
                    if ($modified) {
                        $content['points'] = $newPoints;
                    }
                }

                if ($modified) {
                    $slide->content = $content;
                    $slide->save();
                    $slideCount++;
                }
            }
        }

        $this->info("Fixed {$slideCount} slides");

        // Fix Quizzes
        $quizzes = Quiz::all();
        $quizCount = 0;

        foreach ($quizzes as $quiz) {
            $modified = false;
            $questions = $quiz->questions;

            if (is_array($questions)) {
                foreach ($questions as &$question) {
                    // Fix options: [{value: "opt"}] -> ["opt"]
                    if (isset($question['options']) && is_array($question['options'])) {
                        $newOptions = [];
                        foreach ($question['options'] as $option) {
                            if (is_array($option) && isset($option['value'])) {
                                $newOptions[] = $option['value'];
                                $modified = true;
                            } elseif (is_string($option)) {
                                $newOptions[] = $option;
                            }
                        }
                        if ($modified) {
                            $question['options'] = $newOptions;
                        }
                    }
                }

                if ($modified) {
                    $quiz->questions = $questions;
                    $quiz->save();
                    $quizCount++;
                }
            }
        }

        $this->info("Fixed {$quizCount} quizzes");
        $this->info('Done!');

        return 0;
    }
}
