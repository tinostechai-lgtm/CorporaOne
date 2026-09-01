<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocialLeadService
{
    /**
     * Fetch leads from Facebook Lead Ads
     */
    public function fetchFacebookLeads($pageId, $accessToken)
    {
        $url = "https://graph.facebook.com/v18.0/{$pageId}/leads";
        
        $response = Http::get($url, [
            'access_token' => $accessToken,
            'fields' => 'id,created_time,field_data,ad_id,ad_name,adset_id,adset_name,form_id'
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Facebook API error: ' . $response->body());
        }
        
        $leads = [];
        $data = $response->json();
        
        foreach ($data['data'] ?? [] as $lead) {
            $leadData = [
                'source_id' => $lead['id'],
                'created_time' => $lead['created_time'],
                'ad_name' => $lead['ad_name'] ?? null,
            ];
            
            foreach ($lead['field_data'] ?? [] as $field) {
                $leadData[$field['name']] = $field['values'][0] ?? null;
            }
            
            $leads[] = $this->normalizeFacebookLead($leadData);
        }
        
        return $leads;
    }
    
    /**
     * Normalize Facebook lead data
     */
    protected function normalizeFacebookLead($data)
    {
        return [
            'name' => $data['full_name'] ?? $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone_number'] ?? $data['phone'] ?? null,
            'subject' => $data['ad_name'] ?? 'Facebook Lead',
            'meta' => json_encode($data)
        ];
    }
    
    /**
     * Fetch leads from Instagram (via Instagram Graph API)
     */
    public function fetchInstagramLeads($businessId, $accessToken)
    {
        // Instagram leads come through Facebook Lead Ads with Instagram placements
        $url = "https://graph.facebook.com/v18.0/{$businessId}/leads";
        
        $response = Http::get($url, [
            'access_token' => $accessToken,
            'fields' => 'id,created_time,field_data,ad_name'
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Instagram API error: ' . $response->body());
        }
        
        $leads = [];
        $data = $response->json();
        
        foreach ($data['data'] ?? [] as $lead) {
            $leadData = [
                'source_id' => $lead['id'],
                'created_time' => $lead['created_time'],
                'ad_name' => $lead['ad_name'] ?? 'Instagram Lead',
            ];
            
            foreach ($lead['field_data'] ?? [] as $field) {
                $leadData[$field['name']] = $field['values'][0] ?? null;
            }
            
            $leads[] = $this->normalizeInstagramLead($leadData);
        }
        
        return $leads;
    }
    
    /**
     * Normalize Instagram lead data
     */
    protected function normalizeInstagramLead($data)
    {
        return [
            'name' => $data['full_name'] ?? $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone_number'] ?? $data['phone'] ?? null,
            'subject' => $data['ad_name'] ?? 'Instagram Lead',
            'meta' => json_encode($data)
        ];
    }
    /**
 * Process leads from social media
 */
protected function processSocialLeads($leads, $source)
{
    $creator = $this->getSystemCreator();
    $creatorId = $creator->creatorId() ?? $creator->id;
    
    $pipeline = Pipeline::where('created_by', $creatorId)->first();
    if (!$pipeline) return 0;
    
    $stage = LeadStage::where('pipeline_id', $pipeline->id)->orderBy('order')->first();
    if (!$stage) return 0;
    
    $created = 0;
    foreach ($leads as $leadData) {
        // Check for duplicate by email
        if (!empty($leadData['email']) && Lead::where('email', $leadData['email'])->exists()) {
            continue;
        }
        
        // Check for duplicate by phone if no email
        if (empty($leadData['email']) && !empty($leadData['phone']) && Lead::where('phone', $leadData['phone'])->exists()) {
            continue;
        }
        
        $lead = Lead::create([
            'name' => $leadData['name'] ?? 'Lead from ' . $source,
            'email' => $leadData['email'] ?? null,
            'phone' => $leadData['phone'] ?? null,
            'subject' => $leadData['subject'] ?? 'Lead from ' . ucfirst($source),
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
            'created_by' => $creatorId,
            'user_id' => $creator->id,
            'date' => now()->format('Y-m-d'),
            'lead_source' => $source,
            'lead_score' => 50, // Default score for social leads
        ]);
        
        UserLead::create([
            'user_id' => $creatorId,
            'lead_id' => $lead->id,
        ]);
        
        $created++;
    }
    
    return $created;
}
    
    /**
     * Fetch leads from WhatsApp Business API
     */
    public function fetchWhatsAppLeads($phoneNumberId, $accessToken)
    {
        // WhatsApp Business API for lead generation
        $url = "https://graph.facebook.com/v18.0/{$phoneNumberId}/messages";
        
        // Get recent conversations/messages
        $response = Http::get($url, [
            'access_token' => $accessToken,
            'fields' => 'id,from,text,timestamp'
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('WhatsApp API error: ' . $response->body());
        }
        
        $leads = [];
        $data = $response->json();
        
        foreach ($data['data'] ?? [] as $message) {
            // Only process incoming messages as potential leads
            if ($this->isIncomingLeadMessage($message)) {
                $leads[] = $this->normalizeWhatsAppLead($message);
            }
        }
        
        return $leads;
    }
    
    /**
     * Check if message is from a potential lead
     */
    protected function isIncomingLeadMessage($message)
    {
        // Skip if it's an outgoing message (from business)
        if (isset($message['from']) && isset($message['to'])) {
            return false;
        }
        return true;
    }
    
    /**
     * Normalize WhatsApp lead data
     */
    protected function normalizeWhatsAppLead($message)
    {
        return [
            'name' => $message['contact']['name'] ?? $message['from'] ?? null,
            'phone' => $message['from'] ?? null,
            'subject' => 'WhatsApp Inquiry',
            'message' => $message['text']['body'] ?? null,
            'meta' => json_encode($message)
        ];
    }
    
    /**
     * Setup Facebook webhook subscription
     */
    public function setupFacebookWebhook($pageId, $accessToken, $webhookUrl)
    {
        $url = "https://graph.facebook.com/v18.0/{$pageId}/subscribed_apps";
        
        $response = Http::post($url, [
            'access_token' => $accessToken,
            'subscribed_fields' => 'leadgen'
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Failed to setup Facebook webhook: ' . $response->body());
        }
        
        // Save webhook URL to settings
        $this->saveWebhookSetting('facebook', $webhookUrl);
        
        return $response->json();
    }
    
    /**
     * Setup Instagram webhook subscription
     */
    public function setupInstagramWebhook($businessId, $accessToken, $webhookUrl)
    {
        $url = "https://graph.facebook.com/v18.0/{$businessId}/subscribed_apps";
        
        $response = Http::post($url, [
            'access_token' => $accessToken,
            'subscribed_fields' => 'leadgen'
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Failed to setup Instagram webhook: ' . $response->body());
        }
        
        $this->saveWebhookSetting('instagram', $webhookUrl);
        
        return $response->json();
    }
    
    /**
     * Save webhook configuration
     */
    protected function saveWebhookSetting($platform, $webhookUrl)
    {
        $setting = WebhookSetting::updateOrCreate(
            ['module' => $platform . '_leads', 'created_by' => auth()->user()->creatorId()],
            ['url' => $webhookUrl, 'method' => 'POST', 'status' => 1]
        );
        
        return $setting;
    }
    
    /**
     * Verify Facebook webhook
     */
    public function verifyFacebookWebhook($request)
    {
        $mode = $request->input('hub_mode');
        $token = $request->input('hub_verify_token');
        $challenge = $request->input('hub_challenge');
        
        $expectedToken = setting('facebook_webhook_verify_token');
        
        if ($mode === 'subscribe' && $token === $expectedToken) {
            return $challenge;
        }
        
        return false;
    }
    
    /**
     * Process incoming webhook from Facebook
     */
    public function processFacebookWebhook($payload)
    {
        $leads = [];
        
        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if ($change['field'] === 'leadgen') {
                    $leadData = $this->fetchLeadById($change['value']['leadgen_id'], $change['value']['page_id']);
                    if ($leadData) {
                        $leads[] = $this->normalizeFacebookLead($leadData);
                    }
                }
            }
        }
        
        return $leads;
    }
    
    /**
     * Fetch individual lead by ID
     */
    protected function fetchLeadById($leadId, $pageId)
    {
        $accessToken = $this->getPageAccessToken($pageId);
        
        $url = "https://graph.facebook.com/v18.0/{$leadId}";
        
        $response = Http::get($url, [
            'access_token' => $accessToken,
            'fields' => 'id,created_time,field_data,ad_name'
        ]);
        
        if ($response->successful()) {
            return $response->json();
        }
        
        return null;
    }
    
    /**
     * Get page access token
     */
    protected function getPageAccessToken($pageId)
    {
        // Retrieve from settings or cache
        return setting('facebook_page_access_token');
    }
}