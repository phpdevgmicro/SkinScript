import { useState, useEffect } from "react";
import { useQuery } from "@tanstack/react-query";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
import { Leaf, Mail, Calendar, Eye, Download } from "lucide-react";
import { apiRequest } from "@/lib/queryClient";
import { generateFormulationPDF } from "@/lib/pdf-generator";
import { useToast } from "@/hooks/use-toast";

interface Formulation {
  id: string;
  skinType: string;
  format: string;
  actives: string[];
  extracts: string[];
  hydrators: string[];
  firstName: string;
  lastName: string;
  email: string;
  skinConcerns: string | null;
  safetyScore: number;
  aiSuggestion: string | null;
  createdAt: string;
}

export default function Dashboard() {
  const { toast } = useToast();
  const [email, setEmail] = useState("");
  const [searchEmail, setSearchEmail] = useState("");

  // Query formulations by email
  const { data: formulations, isLoading, error } = useQuery({
    queryKey: ['/api/formulations', searchEmail],
    enabled: !!searchEmail,
    staleTime: 1000 * 60 * 5, // 5 minutes
  });

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (email.trim()) {
      setSearchEmail(email.trim());
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const formatIngredientName = (ingredient: string) => {
    return ingredient.split('-').map(word => 
      word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
  };

  const handleDownloadPDF = (formulation: Formulation) => {
    try {
      const pdfBlob = generateFormulationPDF({
        skinType: formulation.skinType,
        format: formulation.format,
        actives: formulation.actives,
        extracts: formulation.extracts,
        hydrators: formulation.hydrators,
        firstName: formulation.firstName,
        lastName: formulation.lastName,
        email: formulation.email,
        skinConcerns: formulation.skinConcerns || '',
        newsletter: false,
        safetyScore: formulation.safetyScore,
        aiSuggestion: formulation.aiSuggestion || ''
      });
      
      const url = URL.createObjectURL(pdfBlob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `skincraft-${formulation.format}-${formulation.id.slice(0, 8)}.pdf`;
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

  return (
    <div className="min-h-screen bg-warm-gray" data-testid="dashboard-page">
      {/* Header */}
      <header className="bg-white shadow-sm sticky top-0 z-40">
        <div className="container mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-2">
              <Leaf className="text-sage text-2xl" />
              <h1 className="text-2xl font-bold text-charcoal">SkinCraft Dashboard</h1>
            </div>
            <nav className="hidden md:flex space-x-6">
              <a href="/configurator" className="text-charcoal hover:text-sage transition-colors">New Formulation</a>
              <a href="/" className="text-charcoal hover:text-sage transition-colors">Home</a>
            </nav>
          </div>
        </div>
      </header>

      <main className="container mx-auto px-4 py-8">
        {/* Search Section */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Mail className="h-5 w-5" />
              Find Your Formulations
            </CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSearch} className="flex gap-4">
              <Input
                type="email"
                placeholder="Enter your email address"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="flex-1"
                data-testid="input-email"
                required
              />
              <Button type="submit" data-testid="button-search">
                Search Formulations
              </Button>
            </form>
          </CardContent>
        </Card>

        {/* Results Section */}
        {isLoading && (
          <Card>
            <CardContent className="py-8 text-center">
              <p>Loading your formulations...</p>
            </CardContent>
          </Card>
        )}

        {error && (
          <Card>
            <CardContent className="py-8 text-center">
              <p className="text-red-600">Error loading formulations. Please try again.</p>
            </CardContent>
          </Card>
        )}

        {formulations?.success === false && (
          <Card>
            <CardContent className="py-8 text-center">
              <p className="text-gray-600">No formulations found for this email address.</p>
            </CardContent>
          </Card>
        )}

        {formulations?.success && formulations.formulations && (
          <div className="space-y-6">
            <h2 className="text-xl font-semibold">
              Found {formulations.formulations.length} formulation(s) for {searchEmail}
            </h2>

            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
              {formulations.formulations.map((formulation: Formulation) => (
                <Card key={formulation.id} className="hover:shadow-lg transition-shadow" data-testid={`card-formulation-${formulation.id}`}>
                  <CardHeader>
                    <div className="flex justify-between items-start">
                      <CardTitle className="text-lg capitalize">
                        {formulation.format} for {formulation.skinType} Skin
                      </CardTitle>
                      <Badge variant="secondary" className="ml-2">
                        {formulation.safetyScore}% Safe
                      </Badge>
                    </div>
                    <p className="text-sm text-gray-600 flex items-center gap-1">
                      <Calendar className="h-4 w-4" />
                      {formatDate(formulation.createdAt)}
                    </p>
                  </CardHeader>
                  
                  <CardContent className="space-y-4">
                    <div>
                      <p className="font-medium text-sm text-gray-700">Customer:</p>
                      <p className="text-sm">{formulation.firstName} {formulation.lastName}</p>
                    </div>

                    {formulation.actives.length > 0 && (
                      <div>
                        <p className="font-medium text-sm text-gray-700 mb-1">Active Ingredients:</p>
                        <div className="flex flex-wrap gap-1">
                          {formulation.actives.map((active, index) => (
                            <Badge key={index} variant="outline" className="text-xs">
                              {formatIngredientName(active)}
                            </Badge>
                          ))}
                        </div>
                      </div>
                    )}

                    {formulation.extracts.length > 0 && (
                      <div>
                        <p className="font-medium text-sm text-gray-700 mb-1">Extracts:</p>
                        <div className="flex flex-wrap gap-1">
                          {formulation.extracts.map((extract, index) => (
                            <Badge key={index} variant="secondary" className="text-xs">
                              {formatIngredientName(extract)}
                            </Badge>
                          ))}
                        </div>
                      </div>
                    )}

                    {formulation.hydrators.length > 0 && (
                      <div>
                        <p className="font-medium text-sm text-gray-700 mb-1">Hydrators:</p>
                        <div className="flex flex-wrap gap-1">
                          {formulation.hydrators.map((hydrator, index) => (
                            <Badge key={index} variant="default" className="text-xs">
                              {formatIngredientName(hydrator)}
                            </Badge>
                          ))}
                        </div>
                      </div>
                    )}

                    {formulation.skinConcerns && (
                      <div>
                        <p className="font-medium text-sm text-gray-700">Skin Concerns:</p>
                        <p className="text-sm text-gray-600">{formulation.skinConcerns}</p>
                      </div>
                    )}

                    {formulation.aiSuggestion && (
                      <div className="bg-gray-50 p-3 rounded-lg">
                        <p className="font-medium text-sm text-gray-700 mb-1">AI Recommendation:</p>
                        <p className="text-xs text-gray-600 line-clamp-3">{formulation.aiSuggestion}</p>
                      </div>
                    )}

                    <Separator />

                    <div className="flex gap-2">
                      <Button 
                        variant="outline" 
                        size="sm" 
                        className="flex-1"
                        onClick={() => handleDownloadPDF(formulation)}
                        data-testid={`button-download-${formulation.id}`}
                      >
                        <Download className="h-4 w-4 mr-1" />
                        PDF
                      </Button>
                    </div>

                    <p className="text-xs text-gray-500 text-center">
                      ID: {formulation.id}
                    </p>
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>
        )}
      </main>
    </div>
  );
}