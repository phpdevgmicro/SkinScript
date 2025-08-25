import * as brevo from '@getbrevo/brevo';

if (!process.env.BREVO_API_KEY) {
  throw new Error("BREVO_API_KEY environment variable must be set");
}

const apiInstance = new brevo.TransactionalEmailsApi();
apiInstance.setApiKey(brevo.TransactionalEmailsApiApiKeys.apiKey, process.env.BREVO_API_KEY);

interface FormulationEmailData {
  firstName: string;
  lastName: string;
  email: string;
  skinType: string;
  format: string;
  actives: string[];
  extracts: string[];
  hydrators: string[];
  skinConcerns?: string;
  safetyScore: number;
  aiSuggestion?: string;
  formulationId: string;
}

export async function sendFormulationEmail(data: FormulationEmailData): Promise<boolean> {
  try {
    const activesList = data.actives.map(active => 
      active.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
    ).join(', ');
    
    const extractsList = data.extracts.map(extract => 
      extract.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
    ).join(', ');
    
    const hydratorsList = data.hydrators.map(hydrator => 
      hydrator.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
    ).join(', ');

    const emailContent = `
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: Arial, sans-serif; color: #2c2c2c; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
            .formulation-card { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .ingredient-section { margin: 15px 0; }
            .ingredient-title { font-weight: bold; color: #374151; margin-bottom: 5px; }
            .safety-score { background: #10b981; color: white; padding: 5px 10px; border-radius: 20px; font-size: 12px; display: inline-block; }
            .ai-suggestion { background: #e5e7eb; padding: 15px; border-radius: 6px; margin-top: 15px; font-style: italic; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🧴 Your Custom SkinCraft Formulation</h1>
                <p>Personalized skincare just for you!</p>
            </div>
            
            <div class="content">
                <h2>Hello ${data.firstName}!</h2>
                <p>Your custom ${data.format} formulation for ${data.skinType} skin has been created successfully!</p>
                
                <div class="formulation-card">
                    <h3>Your Formulation Details</h3>
                    <p><strong>Format:</strong> ${data.format.charAt(0).toUpperCase() + data.format.slice(1)}</p>
                    <p><strong>Skin Type:</strong> ${data.skinType.charAt(0).toUpperCase() + data.skinType.slice(1)}</p>
                    ${data.skinConcerns ? `<p><strong>Skin Concerns:</strong> ${data.skinConcerns}</p>` : ''}
                    <p><strong>Safety Score:</strong> <span class="safety-score">${data.safetyScore}%</span></p>
                    
                    <div class="ingredient-section">
                        <div class="ingredient-title">Active Ingredients:</div>
                        <p>${activesList || 'None selected'}</p>
                    </div>
                    
                    <div class="ingredient-section">
                        <div class="ingredient-title">Botanical Extracts:</div>
                        <p>${extractsList || 'None selected'}</p>
                    </div>
                    
                    <div class="ingredient-section">
                        <div class="ingredient-title">Hydrators:</div>
                        <p>${hydratorsList || 'None selected'}</p>
                    </div>
                    
                    ${data.aiSuggestion ? `
                    <div class="ai-suggestion">
                        <strong>AI Recommendation:</strong><br>
                        ${data.aiSuggestion}
                    </div>
                    ` : ''}
                </div>
                
                <p><strong>Formulation ID:</strong> ${data.formulationId}</p>
                <p>Keep this ID for future reference to your custom formulation.</p>
                
                <hr style="margin: 30px 0; border: none; border-top: 1px solid #e5e7eb;">
                
                <p><small>This formulation is for informational purposes only. Please consult with a dermatologist before use. Store your custom formulation in a cool, dry place.</small></p>
                
                <p>Thank you for using SkinCraft!</p>
            </div>
        </div>
    </body>
    </html>
    `;

    const sendSmtpEmail = new brevo.SendSmtpEmail();
    sendSmtpEmail.to = [{ email: data.email, name: `${data.firstName} ${data.lastName}` }];
    sendSmtpEmail.sender = { email: "noreply@skincraft.app", name: "SkinCraft" };
    sendSmtpEmail.subject = `🧴 Your Custom ${data.format.charAt(0).toUpperCase() + data.format.slice(1)} Formulation is Ready!`;
    sendSmtpEmail.htmlContent = emailContent;

    await apiInstance.sendTransacEmail(sendSmtpEmail);
    console.log(`Formulation email sent successfully to ${data.email}`);
    return true;
  } catch (error) {
    console.error('Error sending formulation email:', error);
    return false;
  }
}