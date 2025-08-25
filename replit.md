# Build Your Own Energizing Mist - Skincare Formulation Tool

## Overview

This is an interactive web application that allows users to create personalized skincare formulations, specifically energizing mists. The app guides users through a multi-step process to select skin types, base formats, key active ingredients, botanical extracts, and boosters while enforcing compatibility rules between ingredients. The application features a modern, responsive design with form validation and data persistence capabilities.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture
- **Single Page Application (SPA)**: Built with vanilla HTML, CSS, and JavaScript without frameworks
- **Component-Based Design**: Modular CSS classes and JavaScript methods for different form sections
- **Responsive Layout**: Mobile-first design using CSS Grid and Flexbox with clamp() functions for fluid typography
- **Progressive Enhancement**: Form works without JavaScript, with enhanced UX when enabled

### AI-Enhanced Backend Architecture
- **PHP MCP Server**: Custom Model Context Protocol implementation using PostgreSQL database
- **Real-Time Database Integration**: Direct PostgreSQL connection for current ingredient safety data
- **AI-Powered Formulation Engine**: GPT-4o with real-time ingredient database context
- **Database-Verified Compatibility**: Live ingredient compatibility checking and safety validation

### Form Management System
- **Multi-Step Form Flow**: Sections for skin type, base format, key actives, extracts, boosters, and contact information
- **State Management**: Centralized form data object tracking user selections across all sections
- **Validation Engine**: Custom validation rules for required fields, selection limits, and ingredient compatibility
- **Data Persistence**: localStorage integration for saving and restoring user progress

### Ingredient Compatibility System
- **Rule-Based Validation**: Predefined incompatible ingredient combinations (retinol + vitamin C, etc.)
- **Dynamic UI Updates**: Real-time enabling/disabling of options based on current selections
- **Selection Limits**: Maximum of 3 key active ingredients with counter tracking
- **Conflict Prevention**: Automatic validation prevents incompatible ingredient selection

### User Interface Design
- **Modern CSS Architecture**: Custom properties, gradients, and backdrop filters for visual appeal
- **Interactive Components**: Custom checkbox cards, radio buttons, and form controls with hover/focus states
- **Icon Integration**: Font Awesome icons throughout the interface for visual hierarchy
- **Loading States**: Visual feedback for form submission and processing states

### Data Flow Architecture
- **Event-Driven Updates**: DOM event listeners trigger state changes and UI updates
- **Unidirectional Data Flow**: Form data flows from user input → validation → state update → UI refresh
- **Error Handling**: Comprehensive validation with user-friendly error messages
- **Success Feedback**: Confirmation systems for successful form completion

## External Dependencies

### CDN Resources
- **Google Fonts**: Inter font family for typography
- **Font Awesome**: Icon library (v6.4.0) for UI elements
- **Pixabay Images**: External image hosting for hero section visuals

### Browser APIs
- **localStorage**: Client-side data persistence for form state
- **DOM APIs**: Event handling, form validation, and dynamic content updates
- **CSS Features**: Modern CSS properties including backdrop-filter, clamp(), and CSS Grid

### AI and Database Integration
- **OpenAI GPT-4o**: AI-powered formulation generation with real-time ingredient data
- **PHP MCP Server**: Model Context Protocol server providing real-time ingredient database access
- **PostgreSQL Database**: Real-time skincare ingredient database with safety data, compatibility rules, and formulation guidelines
- **SMTP Email Services**: Automated email notifications for form submissions
- **PDF Generation**: Dynamic PDF creation for formulation reports