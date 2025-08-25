import { Dialog, DialogContent } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Check, FileText, Plus } from "lucide-react";

interface SuccessModalProps {
  isOpen: boolean;
  onClose: () => void;
  onViewPDF: () => void;
  onCreateAnother: () => void;
}

export function SuccessModal({ isOpen, onClose, onViewPDF, onCreateAnother }: SuccessModalProps) {
  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-md text-center" data-testid="success-modal">
        <div className="space-y-6 py-4">
          <div className="w-16 h-16 bg-sage/20 rounded-full flex items-center justify-center mx-auto">
            <Check className="w-8 h-8 text-sage" />
          </div>
          
          <div>
            <h3 className="text-xl font-bold mb-2">Formula Created!</h3>
            <p className="text-gray-600">
              Your custom skincare formulation has been saved successfully. 
              You can now view the detailed PDF with your personalized formula.
            </p>
          </div>
          
          <div className="space-y-3">
            <Button 
              onClick={onViewPDF}
              className="w-full bg-sage text-white hover:bg-sage/90"
              data-testid="button-view-pdf"
            >
              <FileText className="w-4 h-4 mr-2" />
              View PDF Preview
            </Button>
            
            <Button 
              variant="outline"
              onClick={onCreateAnother}
              className="w-full"
              data-testid="button-create-another"
            >
              <Plus className="w-4 h-4 mr-2" />
              Create Another Formula
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
}
