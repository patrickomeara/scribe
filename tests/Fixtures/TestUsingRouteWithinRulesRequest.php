<?php

namespace Knuckles\Scribe\Tests\Fixtures;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class TestUsingRouteWithinRulesRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => [$this->enforceTypedPost(Route::current()->parameters()['post']) ? 'required' : 'sometimes'],
            'author' => [$this->enforceTypedPost($this->post) ? 'required' : 'sometimes'],
            'date' => [$this->enforceTypedPost($this->route('post')) ? 'required' : 'sometimes'],
        ];
    }

    protected function enforceTypedPost(TestPost $post): bool
    {
        return true;
    }
}
