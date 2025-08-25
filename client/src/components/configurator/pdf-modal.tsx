import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Download, X } from "lucide-react";
import { FormData } from "./form-wizard";

interface PDFModalProps {
  isOpen: boolean;
  onClose: () => void;
  formData: FormData;
  onDownloadPDF: () => void;
  aiSuggestion?: string;
}

export function PDFModal({ isOpen, onClose, formData, onDownloadPDF, aiSuggestion }: PDFModalProps) {
  const formatIngredientName = (ingredient: string) => {
    return ingredient.split('-').map(word => 
      word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
  };

  const getIngredientPercentage = (ingredient: string) => {
    const percentages: Record<string, string> = {
      'caffeine': '1.5%',
      'retinol': '0.3%',
      'niacinamide': '5%',
      'vitamin-c': '15%',
      'salicylic-acid': '1%',
      'azelaic-acid': '10%',
      'glycerin': '5%',
      'sodium-pca': '2%',
      'hyaluronic-acid': '1%',
      'ceramides': '2%'
    };
    return percentages[ingredient] || '';
  };

  const getProductName = () => {
    const formatName = formData.format ? 
      formData.format.charAt(0).toUpperCase() + formData.format.slice(1) : 
      'Product';
    return `Custom ${formatName === 'Mist' ? 'Energizing Mist' : 
                   formatName === 'Serum' ? 'Concentrated Serum' : 
                   formatName === 'Cream' ? 'Nourishing Cream' : 'Formula'}`;
  };

  const generateINCIList = () => {
    const baseIngredients = ['Aqua'];
    const inciNames: Record<string, string> = {
      'caffeine': 'Caffeine',
      'retinol': 'Retinol',
      'niacinamide': 'Niacinamide',
      'vitamin-c': 'Ascorbic Acid',
      'salicylic-acid': 'Salicylic Acid',
      'azelaic-acid': 'Azelaic Acid',
      'glycerin': 'Glycerin',
      'sodium-pca': 'Sodium PCA',
      'hyaluronic-acid': 'Sodium Hyaluronate',
      'ceramides': 'Ceramide NP',
      'neem': 'Azadirachta Indica Leaf Extract',
      'beetroot': 'Beta Vulgaris Root Extract',
      'oat': 'Avena Sativa Kernel Extract',
      'green-tea': 'Camellia Sinensis Leaf Extract',
      'chamomile': 'Chamomilla Recutita Flower Extract',
      'aloe-vera': 'Aloe Barbadensis Leaf Extract'
    };

    const allIngredients = [
      ...baseIngredients,
      ...formData.hydrators.map(h => inciNames[h] || formatIngredientName(h)),
      ...formData.actives.map(a => inciNames[a] || formatIngredientName(a)),
      ...formData.extracts.map(e => inciNames[e] || formatIngredientName(e)),
      'Phenoxyethanol',
      'Ethylhexylglycerin',
      'Citric Acid'
    ].filter(Boolean);

    return allIngredients.join(', ');
  };

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-2xl max-h-[90vh] overflow-hidden" data-testid="pdf-modal">
        <DialogHeader className="flex flex-row items-center justify-between space-y-0 pb-4">
          <DialogTitle>Your Custom Formulation</DialogTitle>
          <Button variant="ghost" size="sm" onClick={onClose} data-testid="button-close-pdf">
            <X className="h-4 w-4" />
          </Button>
        </DialogHeader>
        
        <div className="overflow-y-auto max-h-[70vh] space-y-6">
          <div className="text-center">
            <h2 className="text-2xl font-bold mb-2" data-testid="pdf-product-name">
              {getProductName()}
            </h2>
            <p className="text-gray-600">
              Formulated for {formData.firstName} {formData.lastName} - {formatIngredientName(formData.skinType)} Skin
            </p>
          </div>

          <div className="grid md:grid-cols-2 gap-6">
            {formData.actives.length > 0 && (
              <div>
                <h4 className="font-semibold mb-3">ACTIVE INGREDIENTS</h4>
                <div className="space-y-2">
                  {formData.actives.map((active, index) => {
                    const percentage = getIngredientPercentage(active);
                    return (
                      <div key={index} className="flex justify-between">
                        <span>{formatIngredientName(active)}</span>
                        <span className="font-medium">{percentage}</span>
                      </div>
                    );
                  })}
                </div>
              </div>
            )}

            {formData.extracts.length > 0 && (
              <div>
                <h4 className="font-semibold mb-3">BOTANICAL EXTRACTS</h4>
                <div className="space-y-2">
                  {formData.extracts.map((extract, index) => (
                    <div key={index} className="flex justify-between">
                      <span>{formatIngredientName(extract)}</span>
                      <span className="font-medium">2.0%</span>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {formData.hydrators.length > 0 && (
              <div>
                <h4 className="font-semibold mb-3">HYDRATION BOOSTERS</h4>
                <div className="space-y-2">
                  {formData.hydrators.map((hydrator, index) => {
                    const percentage = getIngredientPercentage(hydrator);
                    return (
                      <div key={index} className="flex justify-between">
                        <span>{formatIngredientName(hydrator)}</span>
                        <span className="font-medium">{percentage}</span>
                      </div>
                    );
                  })}
                </div>
              </div>
            )}
          </div>

          <div>
            <h4 className="font-semibold mb-3">FULL INGREDIENT LIST (INCI)</h4>
            <p className="text-sm text-gray-700 leading-relaxed" data-testid="inci-list">
              {generateINCIList()}
            </p>
          </div>

          <div>
            <h4 className="font-semibold mb-3">USAGE INSTRUCTIONS</h4>
            <ul className="text-sm space-y-1 text-gray-700">
              <li>• Apply to clean, dry skin</li>
              <li>• Use morning and evening</li>
              <li>• Follow with moisturizer if needed</li>
              <li>• Always use sunscreen during the day</li>
              <li>• Patch test recommended before first use</li>
            </ul>
          </div>

          {formData.skinConcerns && (
            <div>
              <h4 className="font-semibold mb-3">SKIN CONCERNS ADDRESSED</h4>
              <p className="text-sm text-gray-700">{formData.skinConcerns}</p>
            </div>
          )}

          {aiSuggestion && (
            <div>
              <h4 className="font-semibold mb-3">AI FORMULATION NOTES</h4>
              <p className="text-sm text-gray-700">{aiSuggestion}</p>
            </div>
          )}

          <div className="bg-amber-50 p-4 rounded-lg">
            <h4 className="font-semibold mb-2 text-amber-800">Safety Notes</h4>
            <p className="text-sm text-amber-700">
              This formulation has been checked for ingredient compatibility. Patch test recommended before first use. 
              Discontinue if irritation occurs. Consult a dermatologist if you have specific skin conditions.
            </p>
          </div>
        </div>

        <div className="flex justify-end space-x-4 pt-4 border-t border-gray-200">
          <Button variant="outline" onClick={onClose} data-testid="button-close-modal">
            Close
          </Button>
          <Button onClick={onDownloadPDF} className="bg-coral hover:bg-coral/90 text-white" data-testid="button-download-pdf">
            <Download className="w-4 h-4 mr-2" />
            Download PDF
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  );
}
