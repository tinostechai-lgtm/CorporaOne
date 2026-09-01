<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('facebook_lead_id')->nullable()->after('lead_source');
            $table->string('instagram_lead_id')->nullable()->after('facebook_lead_id');
            $table->string('whatsapp_lead_id')->nullable()->after('instagram_lead_id');
            $table->string('social_profile_url')->nullable()->after('whatsapp_lead_id');
            $table->string('social_media_handle')->nullable()->after('social_profile_url');
            $table->integer('lead_score')->default(0)->after('social_media_handle');
            $table->timestamp('assigned_at')->nullable()->after('lead_score');
            $table->timestamp('converted_at')->nullable()->after('assigned_at');
            $table->timestamp('last_contacted_at')->nullable()->after('converted_at');
            $table->timestamp('next_follow_up_at')->nullable()->after('last_contacted_at');
            
            $table->index('lead_source');
            $table->index('lead_score');
            $table->index('next_follow_up_at');
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_lead_id', 'instagram_lead_id', 'whatsapp_lead_id',
                'social_profile_url', 'social_media_handle', 'lead_score',
                'assigned_at', 'converted_at', 'last_contacted_at', 'next_follow_up_at'
            ]);
        });
    }
};