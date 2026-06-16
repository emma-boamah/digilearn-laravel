<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentChatMessage extends Model
{
    protected $fillable = ['agent_chat_session_id', 'role', 'text', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(AgentChatSession::class, 'agent_chat_session_id');
    }
}
