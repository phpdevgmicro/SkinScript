import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Progress } from "@/components/ui/progress";
import { Sparkles, SprayCan, Droplet, Gavel } from "lucide-react";
import { FormData } from "./form-wizard";

interface ProductPreviewProps {
  formData: FormData;
  onGetAISuggestion: () => void;
  isLoadingAI: boolean;
}

export function ProductPreview({ formData, onGetAISuggestion, isLoadingAI }: ProductPreviewProps) {
  const formatIcon = () => {
    switch (formData.format) {
      case 'mist': return <SprayCan className="text-white text-2xl" />;
      case 'serum': return <Droplet className="text-white text-2xl" />;
      case 'cream': return <Gavel className="text-white text-2xl" />;
      default: return <Droplet className="text-white text-2xl" />;
    }
  };

  const getProductName = () => {
    const formatName = formData.format ? 
      formData.format.charAt(0).toUpperCase() + formData.format.slice(1) : 
      'Product';
    return `Custom ${formatName === 'Mist' ? 'Energizing Mist' : 
                   formatName === 'Serum' ? 'Concentrated Serum' : 
                   formatName === 'Cream' ? 'Nourishing Cream' : 'Formula'}`;
  };

  const getSkinTypeDescription = () => {
    return formData.skinType ? `For ${formData.skinType} skin` : 'Custom formulation';
  };

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
    return percentages[ingredient];
  };

  const calculateSafetyScore = () => {
    let score = 100;
    
    // Reduce score for potentially irritating combinations
    if (formData.actives.includes('retinol') && formData.skinType === 'sensitive') score -= 10;
    if (formData.actives.includes('salicylic-acid') && formData.skinType === 'dry') score -= 5;
    if (formData.actives.length > 2) score -= 5;
    
    // Check for incompatible combinations
    const incompatible = [
      ['retinol', 'vitamin-c'],
      ['retinol', 'salicylic-acid'],
      ['vitamin-c', 'niacinamide']
    ];
    
    for (const pair of incompatible) {
      if (formData.actives.includes(pair[0]) && formData.actives.includes(pair[1])) {
        score -= 15;
      }
    }
    
    return Math.max(score, 60); // Minimum score of 60
  };

  const safetyScore = calculateSafetyScore();

  const renderIngredientsList = (ingredients: string[], colorClass: string, showPercentage = false) => {
    if (ingredients.length === 0) {
      return <div className="text-sm text-gray-400 italic">None selected</div>;
    }

    return ingredients.map((ingredient, index) => {
      const percentage = showPercentage ? getIngredientPercentage(ingredient) : null;
      return (
        <div key={index} className={`bg-${colorClass}-50 rounded-lg p-2`}>
          <span className={`text-sm text-${colorClass}-700`}>
            {formatIngredientName(ingredient)}
            {percentage && ` (${percentage})`}
          </span>
        </div>
      );
    });
  };

  return (
    <Card className="bg-white rounded-2xl shadow-lg sticky top-8" data-testid="product-preview">
      <CardContent className="p-6">
        <h3 className="text-xl font-bold mb-4">Your Custom Formula</h3>
        
        {/* Product Visualization */}
        <div className="text-center mb-6">
          <div className="relative mx-auto w-32 h-40 bg-gradient-to-b from-sage/20 to-teal/30 rounded-2xl flex items-end justify-center p-4 mb-4">
            <div className="w-20 h-24 bg-gradient-to-b from-sage to-teal rounded-xl flex items-center justify-center">
              {formatIcon()}
            </div>
          </div>
          <h4 className="font-semibold text-lg" data-testid="product-name">
            {getProductName()}
          </h4>
          <p className="text-sm text-gray-600" data-testid="skin-type-description">
            {getSkinTypeDescription()}
          </p>
        </div>

        {/* Selected Ingredients */}
        <div className="space-y-4">
          <div>
            <h5 className="font-medium text-sm text-gray-700 mb-2">BASE FORMAT</h5>
            <div className="bg-sage/10 rounded-lg p-3">
              <span className="text-sm font-medium text-sage" data-testid="selected-format">
                {formData.format ? formatIngredientName(formData.format) : 'Not selected'}
              </span>
            </div>
          </div>

          <div>
            <h5 className="font-medium text-sm text-gray-700 mb-2">ACTIVE INGREDIENTS</h5>
            <div className="space-y-2" data-testid="selected-actives">
              {renderIngredientsList(formData.actives, 'teal', true)}
            </div>
          </div>

          <div>
            <h5 className="font-medium text-sm text-gray-700 mb-2">BOTANICAL EXTRACTS</h5>
            <div className="space-y-2" data-testid="selected-extracts">
              {renderIngredientsList(formData.extracts, 'emerald')}
            </div>
          </div>

          <div>
            <h5 className="font-medium text-sm text-gray-700 mb-2">HYDRATORS</h5>
            <div className="space-y-2" data-testid="selected-hydrators">
              {renderIngredientsList(formData.hydrators, 'blue', true)}
            </div>
          </div>
        </div>

        {/* AI Suggestion Button */}
        <Button 
          onClick={onGetAISuggestion}
          disabled={isLoadingAI || !formData.skinType || !formData.format}
          className="w-full mt-6 bg-gradient-to-r from-purple-500 to-pink-500 text-white hover:from-purple-600 hover:to-pink-600"
          data-testid="button-ai-suggestion"
        >
          <Sparkles className="w-4 h-4 mr-2" />
          {isLoadingAI ? 'Getting AI Suggestion...' : 'Get AI Formulation Suggestion'}
        </Button>

        {/* Safety Score */}
        <div className="mt-6 p-4 bg-sage/10 rounded-xl">
          <div className="flex items-center justify-between mb-2">
            <span className="text-sm font-medium text-sage">Safety Score</span>
            <span className="text-lg font-bold text-sage" data-testid="safety-score">
              {safetyScore}/100
            </span>
          </div>
          <Progress value={safetyScore} className="h-2 mb-2" data-testid="safety-score-progress" />
          <p className="text-xs text-sage">
            {safetyScore >= 90 ? 'Excellent compatibility with selected ingredients' :
             safetyScore >= 80 ? 'Good compatibility, minor considerations' :
             safetyScore >= 70 ? 'Moderate compatibility, some cautions' :
             'Lower compatibility, review ingredient combinations'}
          </p>
        </div>
      </CardContent>
    </Card>
  );
}
