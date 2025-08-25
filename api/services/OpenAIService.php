<?php
/**
 * OpenAI Service for AI-powered formulation generation
 */

class OpenAIService {
    private $apiKey;
    private $baseUrl = 'https://api.openai.com/v1';
    
    public function __construct() {
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY');
        
        if (!$this->apiKey) {
            throw new Exception('OpenAI API key not found');
        }
    }
    
    /**
     * Generate AI-powered formulation suggestions
     * @param array $formulation User's selections
     * @return array AI-generated suggestions
     */
    public function generateFormulation($formulation) {
        $prompt = $this->buildFormulationPrompt($formulation);
        
        $response = $this->callOpenAI($prompt);
        
        return $this->parseFormulationResponse($response);
    }
    
    /**
     * Build detailed prompt for formulation generation
     * @param array $formulation
     * @return string
     */
    private function buildFormulationPrompt($formulation) {
        $skinTypes = implode(', ', $formulation['skinType']);
        $keyActives = implode(', ', $formulation['keyActives']);
        $extracts = implode(', ', $formulation['extracts']);
        $boosters = implode(', ', $formulation['boosters']);
        $format = $formulation['baseFormat'];
        $concerns = $formulation['contact']['skinConcerns'] ?? 'general skin health';
        
        return "You are a professional cosmetic chemist with expertise in skincare formulation. Create a detailed, safe, and effective skincare formulation based on the following requirements:

**Customer Profile:**
- Skin Types: {$skinTypes}
- Skin Concerns: {$concerns}
- Preferred Format: {$format}

**Selected Ingredients:**
- Key Actives: {$keyActives}
- Botanical Extracts: {$extracts}
- Boosters/Hydrators: {$boosters}

**Requirements:**
1. Provide SAFE concentration percentages for each ingredient
2. Consider ingredient compatibility and pH requirements
3. Include formulation benefits specific to the customer's skin type
4. Provide application instructions
5. Note any warnings or precautions
6. Suggest ingredient synergies and expected results

**Response Format (JSON):**
{
  \"formulation_name\": \"Custom [Format] for [Skin Types]\",
  \"recommended_percentages\": {
    \"ingredient_name\": {\"min\": 0.5, \"max\": 2.0, \"recommended\": 1.0, \"unit\": \"%\"}
  },
  \"expected_benefits\": [\"benefit1\", \"benefit2\"],
  \"application_instructions\": \"Detailed usage instructions\",
  \"compatibility_notes\": \"Important compatibility information\",
  \"warnings\": [\"warning1\", \"warning2\"],
  \"ingredient_synergies\": \"How ingredients work together\",
  \"estimated_results\": \"Timeline and expected outcomes\",
  \"ph_range\": \"5.5-6.5\",
  \"shelf_life\": \"6-12 months\"
}

Focus on safety, efficacy, and professional cosmetic chemistry principles. Ensure all percentages are within safe, regulatory-compliant ranges.";
    }
    
    /**
     * Make API call to OpenAI
     * @param string $prompt
     * @return array
     */
    protected function callOpenAI($prompt) {
        $data = [
            'model' => 'gpt-4o', // Using GPT-4o for better performance and availability
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a professional cosmetic chemist. Respond with valid JSON only.'
                ],
                [
                    'role' => 'user', 
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1500,
            'response_format' => ['type' => 'json_object']
        ];
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/chat/completions');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("OpenAI cURL Error: " . $error);
            throw new Exception("Failed to connect to OpenAI: " . $error);
        }
        
        if ($httpCode !== 200) {
            error_log("OpenAI HTTP Error {$httpCode}: " . $response);
            throw new Exception("OpenAI API returned error: HTTP {$httpCode}");
        }
        
        $decodedResponse = json_decode($response, true);
        
        if (!$decodedResponse) {
            error_log("OpenAI JSON Decode Error: " . $response);
            throw new Exception("Failed to decode OpenAI response");
        }
        
        return $decodedResponse;
    }
    
    /**
     * Parse OpenAI response and extract formulation data
     * @param array $response
     * @return array
     */
    protected function parseFormulationResponse($response) {
        if (!isset($response['choices'][0]['message']['content'])) {
            throw new Exception("Invalid OpenAI response format");
        }
        
        $content = $response['choices'][0]['message']['content'];
        $formulation = json_decode($content, true);
        
        if (!$formulation) {
            error_log("Failed to decode formulation JSON: " . $content);
            throw new Exception("Failed to parse AI formulation response");
        }
        
        // Validate and clean the response
        return [
            'formulation_name' => $formulation['formulation_name'] ?? 'Custom Skincare Formula',
            'recommended_percentages' => $formulation['recommended_percentages'] ?? [],
            'expected_benefits' => $formulation['expected_benefits'] ?? [],
            'application_instructions' => $formulation['application_instructions'] ?? '',
            'compatibility_notes' => $formulation['compatibility_notes'] ?? '',
            'warnings' => $formulation['warnings'] ?? [],
            'ingredient_synergies' => $formulation['ingredient_synergies'] ?? '',
            'estimated_results' => $formulation['estimated_results'] ?? '',
            'ph_range' => $formulation['ph_range'] ?? '5.5-6.5',
            'shelf_life' => $formulation['shelf_life'] ?? '6-12 months',
            'ai_generated' => true,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Generate product description using AI
     * @param array $formulation
     * @param array $aiSuggestions
     * @return string
     */
    public function generateProductDescription($formulation, $aiSuggestions) {
        $prompt = "Create a compelling, professional product description for this custom skincare formulation:

**Product:** {$aiSuggestions['formulation_name']}
**Key Benefits:** " . implode(', ', $aiSuggestions['expected_benefits']) . "
**Format:** {$formulation['baseFormat']}
**Target Skin:** " . implode(', ', $formulation['skinType']) . "

Write a 2-3 paragraph marketing description that highlights the benefits, key ingredients, and why this formulation is perfect for the customer's skin type. Keep it professional but engaging.";

        $data = [
            'model' => 'gpt-4o', // Using GPT-4o for better performance and availability
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a skincare marketing expert. Write compelling, accurate product descriptions.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.8,
            'max_tokens' => 300
        ];
        
        try {
            $response = $this->callOpenAI($prompt);
            return $response['choices'][0]['message']['content'] ?? '';
        } catch (Exception $e) {
            error_log("Failed to generate product description: " . $e->getMessage());
            return "A custom skincare formulation designed specifically for your skin type and concerns.";
        }
    }
}
?>