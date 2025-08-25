import { useState } from "react";
import { useMutation } from "@tanstack/react-query";
import { Leaf } from "lucide-react";
import { FormWizard, FormData } from "@/components/configurator/form-wizard";
import { ProductPreview } from "@/components/configurator/product-preview";
import { PDFModal } from "@/components/configurator/pdf-modal";
import { SuccessModal } from "@/components/configurator/success-modal";
import { generateFormulationPDF } from "@/lib/pdf-generator";
import { apiRequest } from "@/lib/queryClient";
import { useToast } from "@/hooks/use-toast";

export default function Configurator() {
  const { toast } = useToast();
  
  const [formData, setFormData] = useState<FormData>({
    skinType: '',
    format: '',
    actives: [],
    extracts: [],
    hydrators: [],
    firstName: '',
    lastName: '',
    email: '',
    skinConcerns: '',
    newsletter: false,
  });

  const [showSuccessModal, setShowSuccessModal] = useState(false);
  const [showPDFModal, setShowPDFModal] = useState(false);
  const [aiSuggestion, setAiSuggestion] = useState<string>('');

  // Calculate safety score
  const calculateSafetyScore = () => {
    let score = 100;
    if (formData.actives.includes('retinol') && formData.skinType === 'sensitive') score -= 10;
    if (formData.actives.includes('salicylic-acid') && formData.skinType === 'dry') score -= 5;
    if (formData.actives.length > 2) score -= 5;
    
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
    
    return Math.max(score, 60);
  };

  // Submit formulation mutation
  const submitFormulation = useMutation({
    mutationFn: async (data: FormData) => {
      const safetyScore = calculateSafetyScore();
      const response = await apiRequest('POST', '/api/formulations', {
        ...data,
        safetyScore,
        aiSuggestion,
      });
      return response.json();
    },
    onSuccess: () => {
      setShowSuccessModal(true);
      toast({
        title: "Formula Created!",
        description: "Your custom skincare formulation has been saved successfully.",
      });
    },
    onError: (error) => {
      toast({
        title: "Error",
        description: "Failed to save your formulation. Please try again.",
        variant: "destructive",
      });
      console.error('Error submitting formulation:', error);
    },
  });

  // AI suggestion mutation
  const getAISuggestion = useMutation({
    mutationFn: async () => {
      const response = await apiRequest('POST', '/api/ai-suggestion', {
        skinType: formData.skinType,
        format: formData.format,
        actives: formData.actives,
        extracts: formData.extracts,
        hydrators: formData.hydrators,
        skinConcerns: formData.skinConcerns,
      });
      return response.json();
    },
    onSuccess: (data) => {
      setAiSuggestion(data.suggestion.recommendation);
      toast({
        title: "AI Suggestion Received!",
        description: "Check your product preview for personalized recommendations.",
      });
    },
    onError: (error) => {
      toast({
        title: "AI Suggestion Failed",
        description: "Unable to get AI recommendations at this time.",
        variant: "destructive",
      });
      console.error('Error getting AI suggestion:', error);
    },
  });

  const handleFormDataChange = (updates: Partial<FormData>) => {
    setFormData(prev => ({ ...prev, ...updates }));
  };

  const handleSubmit = (data: FormData) => {
    submitFormulation.mutate(data);
  };

  const handleDownloadPDF = () => {
    try {
      const pdfBlob = generateFormulationPDF({
        ...formData,
        safetyScore: calculateSafetyScore(),
        aiSuggestion,
      });
      
      const url = URL.createObjectURL(pdfBlob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `skincraft-formulation-${formData.firstName || 'custom'}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
      
      toast({
        title: "PDF Downloaded",
        description: "Your formulation PDF has been downloaded successfully.",
      });
    } catch (error) {
      toast({
        title: "Download Failed",
        description: "Unable to generate PDF. Please try again.",
        variant: "destructive",
      });
      console.error('Error generating PDF:', error);
    }
  };

  const handleCreateAnother = () => {
    setFormData({
      skinType: '',
      format: '',
      actives: [],
      extracts: [],
      hydrators: [],
      firstName: '',
      lastName: '',
      email: '',
      skinConcerns: '',
      newsletter: false,
    });
    setAiSuggestion('');
    setShowSuccessModal(false);
    setShowPDFModal(false);
  };

  return (
    <div className="min-h-screen bg-warm-gray" data-testid="configurator-page">
      {/* Header */}
      <header className="bg-white shadow-sm sticky top-0 z-40">
        <div className="container mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-2">
              <Leaf className="text-sage text-2xl" />
              <h1 className="text-2xl font-bold text-charcoal">SkinCraft</h1>
            </div>
            <nav className="hidden md:flex space-x-6">
              <a href="#" className="text-charcoal hover:text-sage transition-colors">How It Works</a>
              <a href="#" className="text-charcoal hover:text-sage transition-colors">Ingredients</a>
              <a href="#" className="text-charcoal hover:text-sage transition-colors">About</a>
            </nav>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="container mx-auto px-4 py-8">
        <div className="grid lg:grid-cols-3 gap-8">
          {/* Form Wizard */}
          <div className="lg:col-span-2">
            <FormWizard
              formData={formData}
              onFormDataChange={handleFormDataChange}
              onSubmit={handleSubmit}
              isSubmitting={submitFormulation.isPending}
            />
          </div>

          {/* Product Preview */}
          <div className="lg:col-span-1">
            <ProductPreview
              formData={formData}
              onGetAISuggestion={() => getAISuggestion.mutate()}
              isLoadingAI={getAISuggestion.isPending}
            />
          </div>
        </div>
      </main>

      {/* Modals */}
      <SuccessModal
        isOpen={showSuccessModal}
        onClose={() => setShowSuccessModal(false)}
        onViewPDF={() => {
          setShowSuccessModal(false);
          setShowPDFModal(true);
        }}
        onCreateAnother={handleCreateAnother}
      />

      <PDFModal
        isOpen={showPDFModal}
        onClose={() => setShowPDFModal(false)}
        formData={formData}
        onDownloadPDF={handleDownloadPDF}
        aiSuggestion={aiSuggestion}
      />
    </div>
  );
}
