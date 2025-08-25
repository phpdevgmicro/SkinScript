import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { 
  Settings, 
  Users, 
  FlaskConical, 
  Calendar,
  TrendingUp,
  Download,
  Mail,
  Eye
} from "lucide-react";
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

export default function Admin() {
  const { toast } = useToast();
  const [selectedFormulation, setSelectedFormulation] = useState<Formulation | null>(null);

  // This would need to be a different endpoint that returns all formulations
  // For now, we'll show a placeholder since we'd need admin authentication
  const { data: allFormulations, isLoading } = useQuery({
    queryKey: ['/api/admin/formulations'],
    enabled: false, // Disabled until we implement admin auth
  });

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
      link.download = `admin-formulation-${formulation.id.slice(0, 8)}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
      
      toast({
        title: "PDF Downloaded",
        description: "Formulation PDF downloaded successfully.",
      });
    } catch (error) {
      toast({
        title: "Download Failed",
        description: "Unable to generate PDF. Please try again.",
        variant: "destructive",
      });
    }
  };

  // Mock data for demonstration
  const mockStats = {
    totalFormulations: 156,
    activeUsers: 89,
    avgSafetyScore: 92,
    popularIngredients: ['Niacinamide', 'Hyaluronic Acid', 'Caffeine', 'Vitamin C'],
    recentFormulations: []
  };

  const mockIngredients = [
    { name: 'Caffeine', category: 'Active', safetyLevel: 'Safe', usageCount: 45 },
    { name: 'Retinol', category: 'Active', safetyLevel: 'Caution', usageCount: 32 },
    { name: 'Niacinamide', category: 'Active', safetyLevel: 'Safe', usageCount: 67 },
    { name: 'Hyaluronic Acid', category: 'Hydrator', safetyLevel: 'Safe', usageCount: 78 },
    { name: 'Glycerin', category: 'Hydrator', safetyLevel: 'Safe', usageCount: 54 },
    { name: 'Neem', category: 'Extract', safetyLevel: 'Safe', usageCount: 23 },
    { name: 'Green Tea', category: 'Extract', safetyLevel: 'Safe', usageCount: 34 },
  ];

  return (
    <div className="min-h-screen bg-warm-gray" data-testid="admin-page">
      {/* Header */}
      <header className="bg-white shadow-sm sticky top-0 z-40">
        <div className="container mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-2">
              <Settings className="text-sage text-2xl" />
              <h1 className="text-2xl font-bold text-charcoal">SkinCraft Admin</h1>
            </div>
            <nav className="hidden md:flex space-x-6">
              <a href="/configurator" className="text-charcoal hover:text-sage transition-colors">New Formulation</a>
              <a href="/dashboard" className="text-charcoal hover:text-sage transition-colors">Dashboard</a>
              <a href="/" className="text-charcoal hover:text-sage transition-colors">Home</a>
            </nav>
          </div>
        </div>
      </header>

      <main className="container mx-auto px-4 py-8">
        <Tabs defaultValue="overview" className="space-y-6">
          <TabsList className="grid w-full grid-cols-4">
            <TabsTrigger value="overview">Overview</TabsTrigger>
            <TabsTrigger value="formulations">Formulations</TabsTrigger>
            <TabsTrigger value="ingredients">Ingredients</TabsTrigger>
            <TabsTrigger value="settings">Settings</TabsTrigger>
          </TabsList>

          {/* Overview Tab */}
          <TabsContent value="overview" className="space-y-6">
            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Total Formulations</CardTitle>
                  <FlaskConical className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold" data-testid="text-total-formulations">{mockStats.totalFormulations}</div>
                  <p className="text-xs text-muted-foreground">+12% from last month</p>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Active Users</CardTitle>
                  <Users className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold" data-testid="text-active-users">{mockStats.activeUsers}</div>
                  <p className="text-xs text-muted-foreground">+8% from last month</p>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Avg Safety Score</CardTitle>
                  <TrendingUp className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold" data-testid="text-avg-safety">{mockStats.avgSafetyScore}%</div>
                  <p className="text-xs text-muted-foreground">+2% from last month</p>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Popular Ingredients</CardTitle>
                  <FlaskConical className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="space-y-1">
                    {mockStats.popularIngredients.slice(0, 3).map((ingredient, index) => (
                      <div key={index} className="text-sm">{ingredient}</div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            </div>

            <Card>
              <CardHeader>
                <CardTitle>System Status</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  <div className="flex items-center justify-between">
                    <span>Database Connection</span>
                    <Badge variant="default">✓ Connected</Badge>
                  </div>
                  <div className="flex items-center justify-between">
                    <span>Email Service (Brevo)</span>
                    <Badge variant="default">✓ Active</Badge>
                  </div>
                  <div className="flex items-center justify-between">
                    <span>AI Service (OpenAI)</span>
                    <Badge variant="default">✓ Available</Badge>
                  </div>
                  <div className="flex items-center justify-between">
                    <span>PDF Generation</span>
                    <Badge variant="default">✓ Working</Badge>
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Formulations Tab */}
          <TabsContent value="formulations" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <FlaskConical className="h-5 w-5" />
                  All Formulations
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-center py-8">
                  <p className="text-gray-600 mb-4">
                    Admin authentication and formulation management features would be implemented here.
                  </p>
                  <p className="text-sm text-gray-500">
                    This would include: View all formulations, Filter by date/user, Export data, Delete formulations
                  </p>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Ingredients Tab */}
          <TabsContent value="ingredients" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <FlaskConical className="h-5 w-5" />
                  Ingredient Management
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {mockIngredients.map((ingredient, index) => (
                    <div key={index} className="flex items-center justify-between p-4 border rounded-lg">
                      <div className="space-y-1">
                        <p className="font-medium">{ingredient.name}</p>
                        <div className="flex gap-2">
                          <Badge variant="outline">{ingredient.category}</Badge>
                          <Badge 
                            variant={ingredient.safetyLevel === 'Safe' ? 'default' : 'secondary'}
                          >
                            {ingredient.safetyLevel}
                          </Badge>
                        </div>
                      </div>
                      <div className="text-right">
                        <p className="text-sm font-medium">Used {ingredient.usageCount} times</p>
                        <div className="flex gap-2 mt-2">
                          <Button variant="outline" size="sm">Edit</Button>
                          <Button variant="destructive" size="sm">Remove</Button>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Settings Tab */}
          <TabsContent value="settings" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Settings className="h-5 w-5" />
                  Application Settings
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="space-y-4">
                  <h3 className="font-medium">Email Notifications</h3>
                  <div className="flex items-center justify-between">
                    <span>Send formulation emails to users</span>
                    <Badge variant="default">Enabled</Badge>
                  </div>
                  <div className="flex items-center justify-between">
                    <span>Admin notification emails</span>
                    <Badge variant="secondary">Disabled</Badge>
                  </div>
                </div>

                <Separator />

                <div className="space-y-4">
                  <h3 className="font-medium">AI Features</h3>
                  <div className="flex items-center justify-between">
                    <span>AI-powered recommendations</span>
                    <Badge variant="default">Enabled</Badge>
                  </div>
                  <div className="flex items-center justify-between">
                    <span>Automatic safety scoring</span>
                    <Badge variant="default">Enabled</Badge>
                  </div>
                </div>

                <Separator />

                <div className="space-y-4">
                  <h3 className="font-medium">Database</h3>
                  <div className="flex items-center justify-between">
                    <span>Database provider</span>
                    <Badge variant="outline">Supabase PostgreSQL</Badge>
                  </div>
                  <div className="flex items-center justify-between">
                    <span>Automatic backups</span>
                    <Badge variant="default">Daily</Badge>
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </main>
    </div>
  );
}