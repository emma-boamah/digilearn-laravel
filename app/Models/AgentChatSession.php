<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentChatSession extends Model
{
    protected $fillable = ['user_id', 'title'];

    public function messages()
    {
        return $this->hasMany(AgentChatMessage::class);
    }
}
