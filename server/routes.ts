import type { Express } from "express";
import { createServer, type Server } from "http";
import { storage } from "./storage";
import { insertFormulationSchema } from "@shared/schema";
import { getFormulationSuggestion } from "./services/openai";
import { sendFormulationEmail } from "./services/email";

export async function registerRoutes(app: Express): Promise<Server> {
  // Create a new formulation
  app.post("/api/formulations", async (req, res) => {
    try {
      const validatedData = insertFormulationSchema.parse(req.body);
      const formulation = await storage.createFormulation(validatedData);
      
      // Send email notification to user
      try {
        await sendFormulationEmail({
          firstName: formulation.firstName,
          lastName: formulation.lastName,
          email: formulation.email,
          skinType: formulation.skinType,
          format: formulation.format,
          actives: formulation.actives,
          extracts: formulation.extracts,
          hydrators: formulation.hydrators,
          skinConcerns: formulation.skinConcerns || undefined,
          safetyScore: formulation.safetyScore,
          aiSuggestion: formulation.aiSuggestion || undefined,
          formulationId: formulation.id,
        });
      } catch (emailError) {
        console.error('Failed to send email notification:', emailError);
        // Don't fail the request if email fails
      }
      
      res.json({ success: true, formulation });
    } catch (error) {
      console.error("Error creating formulation:", error);
      res.status(400).json({ 
        success: false, 
        error: error instanceof Error ? error.message : "Failed to create formulation" 
      });
    }
  });

  // Get formulation by ID
  app.get("/api/formulations/:id", async (req, res) => {
    try {
      const formulation = await storage.getFormulation(req.params.id);
      if (!formulation) {
        return res.status(404).json({ success: false, error: "Formulation not found" });
      }
      res.json({ success: true, formulation });
    } catch (error) {
      console.error("Error fetching formulation:", error);
      res.status(500).json({ 
        success: false, 
        error: "Failed to fetch formulation" 
      });
    }
  });

  // Get formulations by email
  app.get("/api/formulations", async (req, res) => {
    try {
      const { email } = req.query;
      if (!email || typeof email !== 'string') {
        return res.status(400).json({ success: false, error: "Email parameter is required" });
      }
      
      const formulations = await storage.getFormulationsByEmail(email);
      res.json({ success: true, formulations });
    } catch (error) {
      console.error("Error fetching formulations:", error);
      res.status(500).json({ 
        success: false, 
        error: "Failed to fetch formulations" 
      });
    }
  });

  // Get AI formulation suggestion
  app.post("/api/ai-suggestion", async (req, res) => {
    try {
      const { skinType, format, actives, extracts, hydrators, skinConcerns } = req.body;
      
      if (!skinType || !format) {
        return res.status(400).json({ 
          success: false, 
          error: "Skin type and format are required" 
        });
      }

      const suggestion = await getFormulationSuggestion(
        skinType,
        format,
        actives || [],
        extracts || [],
        hydrators || [],
        skinConcerns
      );

      res.json({ success: true, suggestion });
    } catch (error) {
      console.error("Error getting AI suggestion:", error);
      res.status(500).json({ 
        success: false, 
        error: error instanceof Error ? error.message : "Failed to get AI suggestion" 
      });
    }
  });

  const httpServer = createServer(app);
  return httpServer;
}
