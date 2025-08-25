import OpenAI from "openai";

// the newest OpenAI model is "gpt-5" which was released August 7, 2025. do not change this unless explicitly requested by the user
const openai = new OpenAI({ 
  apiKey: process.env.OPENAI_API_KEY || process.env.OPENAI_API_KEY_ENV_VAR || "default_key"
});

export interface FormulationSuggestion {
  recommendation: string;
  concentrations: Record<string, string>;
  additionalIngredients: string[];
  warnings: string[];
  benefits: string[];
}

export async function getFormulationSuggestion(
  skinType: string,
  format: string,
  actives: string[],
  extracts: string[],
  hydrators: string[],
  skinConcerns?: string
): Promise<FormulationSuggestion> {
  try {
    const prompt = `You are a professional cosmetic chemist. Based on the following skincare formulation request, provide detailed recommendations:

Skin Type: ${skinType}
Format: ${format}
Active Ingredients: ${actives.join(', ')}
Botanical Extracts: ${extracts.join(', ')}
Hydrators: ${hydrators.join(', ')}
${skinConcerns ? `Skin Concerns: ${skinConcerns}` : ''}

Please provide a JSON response with:
1. A professional recommendation summary
2. Recommended concentrations for each active ingredient
3. Any additional ingredients that would complement this formulation
4. Important warnings or contraindications
5. Expected benefits of this formulation

Ensure all concentration recommendations are safe and within industry standards.`;

    const response = await openai.chat.completions.create({
      model: "gpt-4o-mini",
      messages: [
        {
          role: "system",
          content: "You are an expert cosmetic chemist with 20+ years of experience in skincare formulation. Provide scientifically accurate, safe recommendations in JSON format."
        },
        {
          role: "user",
          content: prompt
        }
      ],
      response_format: { type: "json_object" },
      max_tokens: 1000,
    });

    const result = JSON.parse(response.choices[0].message.content || '{}');
    
    return {
      recommendation: result.recommendation || "Custom formulation created based on your selections.",
      concentrations: result.concentrations || {},
      additionalIngredients: result.additionalIngredients || [],
      warnings: result.warnings || [],
      benefits: result.benefits || []
    };
  } catch (error) {
    console.error("OpenAI API error:", error);
    throw new Error("Failed to generate AI formulation suggestion");
  }
}
