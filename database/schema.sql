-- Skincare Formulation Database Schema
-- This schema defines the structure for storing skincare formulation requests

-- Main table for storing formulation submissions
CREATE TABLE IF NOT EXISTS skincare_formulations (
    id SERIAL PRIMARY KEY,
    
    -- Contact Information
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    skin_concerns TEXT,
    
    -- Formulation Details
    skin_type TEXT[] NOT NULL,           -- Array of skin types: ['oily', 'dry', 'sensitive', 'combination']
    base_format VARCHAR(50) NOT NULL,    -- 'mist', 'serum', 'cream'
    key_actives TEXT[] NOT NULL,         -- Array of active ingredients
    extracts TEXT[],                     -- Array of botanical extracts
    boosters TEXT[],                     -- Array of boosters/hydrators
    
    -- Metadata
    user_agent TEXT,
    screen_resolution VARCHAR(50),
    form_version VARCHAR(10) DEFAULT '1.0',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for tracking ingredient compatibility rules
CREATE TABLE IF NOT EXISTS ingredient_compatibility (
    id SERIAL PRIMARY KEY,
    ingredient_1 VARCHAR(100) NOT NULL,
    ingredient_2 VARCHAR(100) NOT NULL,
    compatibility_status VARCHAR(20) NOT NULL, -- 'incompatible', 'caution', 'compatible'
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Ensure unique combinations
    UNIQUE(ingredient_1, ingredient_2)
);

-- Table for storing ingredient information
CREATE TABLE IF NOT EXISTS ingredients (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    category VARCHAR(50) NOT NULL, -- 'active', 'extract', 'booster', 'base'
    description TEXT,
    benefits TEXT[],
    skin_types TEXT[], -- Which skin types this ingredient is good for
    concentration_range VARCHAR(50), -- e.g., "0.1-2%"
    ph_range VARCHAR(20),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for storing formulation status and processing
CREATE TABLE IF NOT EXISTS formulation_status (
    id SERIAL PRIMARY KEY,
    formulation_id INTEGER REFERENCES skincare_formulations(id),
    status VARCHAR(50) NOT NULL, -- 'submitted', 'reviewed', 'approved', 'rejected', 'completed'
    notes TEXT,
    processed_by VARCHAR(255),
    processed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for better performance
CREATE INDEX IF NOT EXISTS idx_formulations_email ON skincare_formulations(email);
CREATE INDEX IF NOT EXISTS idx_formulations_created_at ON skincare_formulations(created_at);
CREATE INDEX IF NOT EXISTS idx_formulations_base_format ON skincare_formulations(base_format);
CREATE INDEX IF NOT EXISTS idx_ingredients_category ON ingredients(category);
CREATE INDEX IF NOT EXISTS idx_ingredients_name ON ingredients(name);
CREATE INDEX IF NOT EXISTS idx_status_formulation_id ON formulation_status(formulation_id);

-- Insert default ingredient data
INSERT INTO ingredients (name, category, description, benefits, skin_types, concentration_range) VALUES
-- Key Actives
('caffeine', 'active', 'Energizing compound that helps reduce puffiness and improve circulation', '{"anti-puffiness", "energizing", "antioxidant"}', '{"oily", "combination", "normal"}', '0.5-2%'),
('l-carnitine', 'active', 'Amino acid that helps with cellular energy production', '{"energizing", "metabolism-boosting", "anti-aging"}', '{"all"}', '1-3%'),
('retinol', 'active', 'Vitamin A derivative for anti-aging and skin renewal', '{"anti-aging", "cell-renewal", "texture-improvement"}', '{"normal", "oily", "combination"}', '0.1-1%'),
('niacinamide', 'active', 'Vitamin B3 for pore refinement and oil control', '{"pore-minimizing", "oil-control", "brightening"}', '{"oily", "combination", "sensitive"}', '2-10%'),
('vitamin-c', 'active', 'Powerful antioxidant for brightening and protection', '{"brightening", "antioxidant", "collagen-support"}', '{"all"}', '5-20%'),
('hyaluronic-acid', 'active', 'Humectant that holds up to 1000x its weight in water', '{"hydrating", "plumping", "moisture-retention"}', '{"dry", "sensitive", "normal"}', '0.1-2%'),

-- Extracts
('beta-vulgaris', 'extract', 'Beet root extract rich in antioxidants and vitamins', '{"antioxidant", "energizing", "circulation-boosting"}', '{"all"}', '1-5%'),
('avena-sativa', 'extract', 'Oat extract with soothing and moisturizing properties', '{"soothing", "moisturizing", "anti-inflammatory"}', '{"sensitive", "dry"}', '1-3%'),
('neem', 'extract', 'Antibacterial and anti-inflammatory plant extract', '{"antibacterial", "anti-inflammatory", "purifying"}', '{"oily", "combination"}', '0.5-2%'),
('bilberry', 'extract', 'Rich in antioxidants and vitamins for skin protection', '{"antioxidant", "brightening", "protective"}', '{"all"}', '1-3%'),
('green-tea', 'extract', 'Antioxidant-rich extract with anti-inflammatory benefits', '{"antioxidant", "anti-inflammatory", "soothing"}', '{"sensitive", "oily", "combination"}', '1-5%'),
('chamomile', 'extract', 'Gentle, soothing extract for sensitive skin', '{"soothing", "anti-inflammatory", "calming"}', '{"sensitive", "dry"}', '1-3%'),

-- Boosters/Hydrators
('glycerin', 'booster', 'Humectant that draws moisture to the skin', '{"hydrating", "moisture-retention", "smoothing"}', '{"dry", "normal"}', '3-10%'),
('sodium-pca', 'booster', 'Natural moisturizing factor found in healthy skin', '{"hydrating", "moisture-balance", "skin-barrier"}', '{"all"}', '1-5%'),
('copper-peptides', 'booster', 'Peptides that support collagen production and healing', '{"anti-aging", "healing", "collagen-support"}', '{"normal", "dry"}', '0.1-1%'),
('ceramides', 'booster', 'Lipids that strengthen the skin barrier', '{"barrier-repair", "moisture-retention", "protective"}', '{"dry", "sensitive"}', '1-5%'),
('squalane', 'booster', 'Lightweight oil that mimics skin''s natural sebum', '{"moisturizing", "barrier-repair", "non-comedogenic"}', '{"all"}', '2-10%')

ON CONFLICT (name) DO NOTHING;

-- Insert ingredient compatibility rules
INSERT INTO ingredient_compatibility (ingredient_1, ingredient_2, compatibility_status, notes) VALUES
('retinol', 'vitamin-c', 'incompatible', 'Can cause irritation when used together. Use at different times.'),
('retinol', 'niacinamide', 'caution', 'May cause irritation in sensitive skin. Start slowly.'),
('vitamin-c', 'niacinamide', 'caution', 'Can reduce effectiveness when mixed. Use at different times for best results.')

ON CONFLICT (ingredient_1, ingredient_2) DO NOTHING;