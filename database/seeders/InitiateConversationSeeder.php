<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class InitiateConversationSeeder extends Seeder
{
    public function run(): void
    {
        $userId = '3f405662-e611-4eec-b510-17b3f56b5b22'; 
        $providerId = '39da1cf6-7f0f-48ed-8bbc-0cb5dd71ddd3'; 

       
        $conversation = Conversation::firstOrCreate(
            ['user_id' => $userId, 'provider_id' => $providerId],
            ['conversation_id' => Str::uuid(), 'last_message_time' => Carbon::now()]
        );

        
        Message::create([
            'message_id' => Str::uuid(),
            'conversation_id' => $conversation->conversation_id,
            'sender_id' => $userId,
            'sender_type' => 'user',
            'message' => 'Hello! I would like to inquire about your services.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        
        $conversation->update(['last_message_time' => Carbon::now()]);
    }
}