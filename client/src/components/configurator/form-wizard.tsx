import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Checkbox } from "@/components/ui/checkbox";
import { Progress } from "@/components/ui/progress";
import { 
  Droplet, 
  Sun, 
  Heart, 
  CircleDot, 
  SprayCan, 
  DropletOff, 
  Gavel,
  TriangleAlert,
  ArrowLeft,
  ArrowRight,
  FlaskConical
} from "lucide-react";

export interface FormData {
  skinType: string;
  format: string;
  actives: string[];
  extracts: string[];
  hydrators: string[];
  firstName: string;
  lastName: string;
  email: string;
  skinConcerns: string;
  newsletter: boolean;
}

interface FormWizardProps {
  formData: FormData;
  onFormDataChange: (data: Partial<FormData>) => void;
  onSubmit: (data: FormData) => void;
  isSubmitting: boolean;
}

const skinTypes = [
  { value: 'oily', label: 'Oily', description: 'Prone to shine, enlarged pores', icon: Droplet, gradient: 'from-sage to-teal' },
  { value: 'dry', label: 'Dry', description: 'Feels tight, flaky, rough texture', icon: Sun, gradient: 'from-coral to-orange-400' },
  { value: 'sensitive', label: 'Sensitive', description: 'Easily irritated, reactive', icon: Heart, gradient: 'from-pink-400 to-rose-400' },
  { value: 'combination', label: 'Combination', description: 'Oily T-zone, dry cheeks', icon: CircleDot, gradient: 'from-purple-400 to-indigo-400' },
];

const formats = [
  { value: 'mist', label: 'Mist', description: 'Light, refreshing application', icon: SprayCan, gradient: 'from-teal to-cyan-400' },
  { value: 'serum', label: 'Serum', description: 'Concentrated, fast-absorbing', icon: DropletOff, gradient: 'from-sage to-green-400' },
  { value: 'cream', label: 'Cream', description: 'Rich, nourishing texture', icon: Gavel, gradient: 'from-coral to-amber-400' },
];

const activeIngredients = [
  { value: 'caffeine', label: 'Caffeine', description: 'Energizing, reduces puffiness', safety: 'Safe: 0.5-2%', safetyColor: 'sage' },
  { value: 'retinol', label: 'Retinol', description: 'Anti-aging, smoothing', safety: 'Caution: 0.1-0.5%', safetyColor: 'coral' },
  { value: 'niacinamide', label: 'Niacinamide', description: 'Pore-refining, brightening', safety: 'Safe: 2-10%', safetyColor: 'sage' },
  { value: 'vitamin-c', label: 'Vitamin C', description: 'Antioxidant, brightening', safety: 'Unstable: 10-20%', safetyColor: 'amber-600' },
  { value: 'salicylic-acid', label: 'Salicylic Acid', description: 'Exfoliating, acne-fighting', safety: 'Caution: 0.5-2%', safetyColor: 'coral' },
  { value: 'azelaic-acid', label: 'Azelaic Acid', description: 'Gentle exfoliant, anti-inflammatory', safety: 'Safe: 5-20%', safetyColor: 'sage' },
];

const botanicalExtracts = [
  { value: 'neem', label: 'Neem', description: 'Purifying, antibacterial' },
  { value: 'beetroot', label: 'Beetroot', description: 'Natural tinting, antioxidant' },
  { value: 'oat', label: 'Oat', description: 'Soothing, calming' },
  { value: 'green-tea', label: 'Green Tea', description: 'Anti-inflammatory, protective' },
  { value: 'chamomile', label: 'Chamomile', description: 'Gentle, soothing' },
  { value: 'aloe-vera', label: 'Aloe Vera', description: 'Hydrating, healing' },
];

const hydrators = [
  { value: 'glycerin', label: 'Glycerin', description: 'Humectant, draws moisture to skin', concentration: '3-10%', icon: DropletOff, gradient: 'from-blue-400 to-cyan-400' },
  { value: 'sodium-pca', label: 'Sodium PCA', description: 'Natural moisturizing factor', concentration: '1-5%', icon: DropletOff, gradient: 'from-teal to-emerald-400' },
  { value: 'hyaluronic-acid', label: 'Hyaluronic Acid', description: 'Intense hydration, plumping', concentration: '0.1-2%', icon: DropletOff, gradient: 'from-purple-400 to-indigo-400' },
  { value: 'ceramides', label: 'Ceramides', description: 'Barrier repair, long-lasting moisture', concentration: '0.5-5%', icon: DropletOff, gradient: 'from-amber-400 to-orange-400' },
];

export function FormWizard({ formData, onFormDataChange, onSubmit, isSubmitting }: FormWizardProps) {
  const [currentStep, setCurrentStep] = useState(1);
  const totalSteps = 6;

  const updateFormData = (updates: Partial<FormData>) => {
    onFormDataChange({ ...formData, ...updates });
  };

  const validateCurrentStep = () => {
    switch (currentStep) {
      case 1: return formData.skinType !== '';
      case 2: return formData.format !== '';
      case 3: return formData.actives.length > 0;
      case 4: return true; // Optional step
      case 5: return true; // Optional step
      case 6: return formData.firstName && formData.email;
      default: return true;
    }
  };

  const nextStep = () => {
    if (validateCurrentStep() && currentStep < totalSteps) {
      setCurrentStep(currentStep + 1);
    }
  };

  const previousStep = () => {
    if (currentStep > 1) {
      setCurrentStep(currentStep - 1);
    }
  };

  const handleSubmit = () => {
    if (validateCurrentStep()) {
      onSubmit(formData);
    }
  };

  const toggleActive = (value: string) => {
    const newActives = formData.actives.includes(value)
      ? formData.actives.filter(a => a !== value)
      : formData.actives.length < 3
      ? [...formData.actives, value]
      : formData.actives;
    updateFormData({ actives: newActives });
  };

  const toggleExtract = (value: string) => {
    const newExtracts = formData.extracts.includes(value)
      ? formData.extracts.filter(e => e !== value)
      : [...formData.extracts, value];
    updateFormData({ extracts: newExtracts });
  };

  const toggleHydrator = (value: string) => {
    const newHydrators = formData.hydrators.includes(value)
      ? formData.hydrators.filter(h => h !== value)
      : [...formData.hydrators, value];
    updateFormData({ hydrators: newHydrators });
  };

  const checkCompatibility = () => {
    const incompatible = [
      ['retinol', 'vitamin-c'],
      ['retinol', 'salicylic-acid'],
      ['vitamin-c', 'niacinamide']
    ];

    for (const pair of incompatible) {
      if (formData.actives.includes(pair[0]) && formData.actives.includes(pair[1])) {
        return `Warning: ${pair[0]} and ${pair[1]} may not be compatible for sensitive skin.`;
      }
    }
    return 'Selected ingredients are compatible. Recommended concentrations will be suggested.';
  };

  const progress = (currentStep / totalSteps) * 100;

  return (
    <Card className="w-full bg-white rounded-2xl shadow-lg" data-testid="form-wizard">
      <CardContent className="p-8">
        {/* Progress Indicator */}
        <div className="mb-8">
          <div className="flex items-center justify-between mb-4">
            <span className="text-sm font-medium text-sage" data-testid="step-indicator">
              Step {currentStep} of {totalSteps}
            </span>
            <span className="text-sm text-gray-500">Build Your Formula</span>
          </div>
          <Progress value={progress} className="h-2" data-testid="progress-bar" />
        </div>

        {/* Step 1: Skin Type */}
        {currentStep === 1 && (
          <div className="space-y-6">
            <div>
              <h2 className="text-2xl font-bold mb-2">What's your skin type?</h2>
              <p className="text-gray-600 mb-6">This helps us recommend the right base and concentration levels.</p>
            </div>
            
            <div className="grid md:grid-cols-2 gap-4">
              {skinTypes.map((type) => {
                const Icon = type.icon;
                const isSelected = formData.skinType === type.value;
                return (
                  <button
                    key={type.value}
                    onClick={() => updateFormData({ skinType: type.value })}
                    className={`p-6 rounded-xl border-2 transition-all duration-200 text-left ${
                      isSelected 
                        ? 'border-sage ring-2 ring-sage/20' 
                        : 'border-gray-200 hover:border-sage'
                    }`}
                    data-testid={`skin-type-${type.value}`}
                  >
                    <div className="flex items-center space-x-4">
                      <div className={`w-12 h-12 bg-gradient-to-br ${type.gradient} rounded-full flex items-center justify-center`}>
                        <Icon className="w-6 h-6 text-white" />
                      </div>
                      <div>
                        <h3 className="font-semibold text-lg">{type.label}</h3>
                        <p className="text-sm text-gray-600">{type.description}</p>
                      </div>
                    </div>
                  </button>
                );
              })}
            </div>
          </div>
        )}

        {/* Step 2: Base Format */}
        {currentStep === 2 && (
          <div className="space-y-6">
            <div>
              <h2 className="text-2xl font-bold mb-2">Choose your format</h2>
              <p className="text-gray-600 mb-6">Different formats work better for different skin types and preferences.</p>
            </div>
            
            <div className="grid md:grid-cols-3 gap-4">
              {formats.map((format) => {
                const Icon = format.icon;
                const isSelected = formData.format === format.value;
                return (
                  <button
                    key={format.value}
                    onClick={() => updateFormData({ format: format.value })}
                    className={`p-6 rounded-xl border-2 transition-all duration-200 text-center ${
                      isSelected 
                        ? 'border-sage ring-2 ring-sage/20' 
                        : 'border-gray-200 hover:border-sage'
                    }`}
                    data-testid={`format-${format.value}`}
                  >
                    <div className={`w-16 h-16 bg-gradient-to-br ${format.gradient} rounded-full flex items-center justify-center mx-auto mb-4`}>
                      <Icon className="w-8 h-8 text-white" />
                    </div>
                    <h3 className="font-semibold text-lg mb-2">{format.label}</h3>
                    <p className="text-sm text-gray-600">{format.description}</p>
                  </button>
                );
              })}
            </div>
          </div>
        )}

        {/* Step 3: Key Actives */}
        {currentStep === 3 && (
          <div className="space-y-6">
            <div>
              <h2 className="text-2xl font-bold mb-2">Select key actives</h2>
              <p className="text-gray-600 mb-2">Choose up to 3 active ingredients for targeted results.</p>
              <p className="text-sm text-amber-600 mb-6 flex items-center">
                <TriangleAlert className="w-4 h-4 mr-1" />
                Selected combination will be checked for safety compatibility
              </p>
            </div>
            
            <div className="grid md:grid-cols-2 gap-4">
              {activeIngredients.map((ingredient) => {
                const isSelected = formData.actives.includes(ingredient.value);
                const canSelect = formData.actives.length < 3 || isSelected;
                return (
                  <button
                    key={ingredient.value}
                    onClick={() => canSelect && toggleActive(ingredient.value)}
                    disabled={!canSelect}
                    className={`p-4 rounded-xl border-2 transition-all duration-200 text-left ${
                      isSelected 
                        ? 'border-sage ring-2 ring-sage/20' 
                        : canSelect 
                        ? 'border-gray-200 hover:border-sage' 
                        : 'border-gray-200 opacity-50 cursor-not-allowed'
                    }`}
                    data-testid={`active-${ingredient.value}`}
                  >
                    <div className="flex items-start justify-between">
                      <div className="flex-1">
                        <h3 className="font-semibold text-lg">{ingredient.label}</h3>
                        <p className="text-sm text-gray-600 mb-2">{ingredient.description}</p>
                        <span className={`text-xs px-2 py-1 rounded-full ${
                          ingredient.safetyColor === 'sage' ? 'bg-sage/10 text-sage' :
                          ingredient.safetyColor === 'coral' ? 'bg-coral/10 text-coral' :
                          'bg-amber-500/10 text-amber-600'
                        }`}>
                          {ingredient.safety}
                        </span>
                      </div>
                      <Checkbox 
                        checked={isSelected} 
                        disabled={!canSelect}
                        className="mt-1"
                      />
                    </div>
                  </button>
                );
              })}
            </div>
            
            <div className="p-4 bg-amber-50 rounded-lg">
              <div className="flex items-start space-x-2">
                <TriangleAlert className="w-5 h-5 text-amber-500 mt-0.5" />
                <div>
                  <p className="text-sm font-medium text-amber-800">Compatibility Check</p>
                  <p className="text-xs text-amber-700 mt-1" data-testid="compatibility-message">
                    {checkCompatibility()}
                  </p>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Step 4: Functional Extracts */}
        {currentStep === 4 && (
          <div className="space-y-6">
            <div>
              <h2 className="text-2xl font-bold mb-2">Add functional extracts</h2>
              <p className="text-gray-600 mb-6">Botanical extracts for additional skin benefits and natural enhancement.</p>
            </div>
            
            <div className="grid md:grid-cols-3 gap-4">
              {botanicalExtracts.map((extract) => {
                const isSelected = formData.extracts.includes(extract.value);
                return (
                  <button
                    key={extract.value}
                    onClick={() => toggleExtract(extract.value)}
                    className={`p-4 rounded-xl border-2 transition-all duration-200 text-center ${
                      isSelected 
                        ? 'border-sage ring-2 ring-sage/20' 
                        : 'border-gray-200 hover:border-sage'
                    }`}
                    data-testid={`extract-${extract.value}`}
                  >
                    <div className="w-full h-32 bg-gray-100 rounded-lg mb-3 flex items-center justify-center">
                      <span className="text-gray-400 text-sm">{extract.label}</span>
                    </div>
                    <h3 className="font-semibold">{extract.label}</h3>
                    <p className="text-sm text-gray-600">{extract.description}</p>
                  </button>
                );
              })}
            </div>
          </div>
        )}

        {/* Step 5: Boosters/Hydrators */}
        {currentStep === 5 && (
          <div className="space-y-6">
            <div>
              <h2 className="text-2xl font-bold mb-2">Choose hydration boosters</h2>
              <p className="text-gray-600 mb-6">Select hydrating and texture-enhancing ingredients.</p>
            </div>
            
            <div className="space-y-4">
              {hydrators.map((hydrator) => {
                const Icon = hydrator.icon;
                const isSelected = formData.hydrators.includes(hydrator.value);
                return (
                  <button
                    key={hydrator.value}
                    onClick={() => toggleHydrator(hydrator.value)}
                    className={`w-full p-4 rounded-xl border-2 transition-all duration-200 text-left ${
                      isSelected 
                        ? 'border-sage ring-2 ring-sage/20' 
                        : 'border-gray-200 hover:border-sage'
                    }`}
                    data-testid={`hydrator-${hydrator.value}`}
                  >
                    <div className="flex items-center justify-between">
                      <div className="flex items-center space-x-4">
                        <div className={`w-12 h-12 bg-gradient-to-br ${hydrator.gradient} rounded-full flex items-center justify-center`}>
                          <Icon className="w-6 h-6 text-white" />
                        </div>
                        <div>
                          <h3 className="font-semibold text-lg">{hydrator.label}</h3>
                          <p className="text-sm text-gray-600">{hydrator.description}</p>
                          <span className="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded-full">
                            Concentration: {hydrator.concentration}
                          </span>
                        </div>
                      </div>
                      <Checkbox checked={isSelected} />
                    </div>
                  </button>
                );
              })}
            </div>
          </div>
        )}

        {/* Step 6: Contact Information */}
        {currentStep === 6 && (
          <div className="space-y-6">
            <div>
              <h2 className="text-2xl font-bold mb-2">Almost done!</h2>
              <p className="text-gray-600 mb-6">Enter your details to receive your custom formulation.</p>
            </div>
            
            <div className="space-y-4">
              <div className="grid md:grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="firstName">First Name</Label>
                  <Input
                    id="firstName"
                    value={formData.firstName}
                    onChange={(e) => updateFormData({ firstName: e.target.value })}
                    placeholder="Enter your first name"
                    data-testid="input-firstName"
                  />
                </div>
                <div>
                  <Label htmlFor="lastName">Last Name</Label>
                  <Input
                    id="lastName"
                    value={formData.lastName}
                    onChange={(e) => updateFormData({ lastName: e.target.value })}
                    placeholder="Enter your last name"
                    data-testid="input-lastName"
                  />
                </div>
              </div>
              
              <div>
                <Label htmlFor="email">Email Address</Label>
                <Input
                  id="email"
                  type="email"
                  value={formData.email}
                  onChange={(e) => updateFormData({ email: e.target.value })}
                  placeholder="Enter your email"
                  data-testid="input-email"
                />
              </div>
              
              <div>
                <Label htmlFor="skinConcerns">Skin Concerns (Optional)</Label>
                <Textarea
                  id="skinConcerns"
                  value={formData.skinConcerns}
                  onChange={(e) => updateFormData({ skinConcerns: e.target.value })}
                  placeholder="Tell us about any specific skin concerns or goals..."
                  className="resize-none"
                  rows={3}
                  data-testid="textarea-skinConcerns"
                />
              </div>
              
              <div className="flex items-start space-x-3">
                <Checkbox
                  id="newsletter"
                  checked={formData.newsletter}
                  onCheckedChange={(checked) => updateFormData({ newsletter: !!checked })}
                  data-testid="checkbox-newsletter"
                />
                <Label htmlFor="newsletter" className="text-sm text-gray-600">
                  I'd like to receive skincare tips and product updates via email
                </Label>
              </div>
            </div>
          </div>
        )}

        {/* Navigation Buttons */}
        <div className="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
          <Button
            variant="outline"
            onClick={previousStep}
            className={`${currentStep === 1 ? 'invisible' : 'visible'}`}
            data-testid="button-previous"
          >
            <ArrowLeft className="w-4 h-4 mr-2" />
            Previous
          </Button>
          
          <div className="flex space-x-4">
            {currentStep < totalSteps ? (
              <Button
                onClick={nextStep}
                disabled={!validateCurrentStep()}
                className="bg-sage hover:bg-sage/90 text-white"
                data-testid="button-next"
              >
                Next Step
                <ArrowRight className="w-4 h-4 ml-2" />
              </Button>
            ) : (
              <Button
                onClick={handleSubmit}
                disabled={!validateCurrentStep() || isSubmitting}
                className="bg-coral hover:bg-coral/90 text-white"
                data-testid="button-submit"
              >
                {isSubmitting ? (
                  'Creating Formula...'
                ) : (
                  <>
                    Create My Formula
                    <FlaskConical className="w-4 h-4 ml-2" />
                  </>
                )}
              </Button>
            )}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
