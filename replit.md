# Overview

This is a full-stack skincare formulation configurator application that allows users to create custom skincare products by selecting ingredients and providing personal information. The app generates personalized AI-powered recommendations, calculates safety scores, and produces downloadable PDF reports of the custom formulations.

The application features a step-by-step wizard interface where users select their skin type, product format (mist, serum, cream), active ingredients, botanical extracts, and hydrators. After collecting user details, the system processes the formulation through an AI service to provide professional recommendations and generates a comprehensive PDF document with ingredient lists, safety information, and usage instructions.

# User Preferences

Preferred communication style: Simple, everyday language.

# System Architecture

## Frontend Architecture
- **Framework**: React with TypeScript using Vite as the build tool
- **UI Library**: Radix UI primitives with shadcn/ui components for consistent design
- **Styling**: Tailwind CSS with CSS custom properties for theming
- **State Management**: React Query (TanStack Query) for server state management
- **Routing**: Wouter for lightweight client-side routing
- **Form Handling**: React Hook Form with Zod schema validation

The frontend follows a component-based architecture with reusable UI components. The main application flow is handled through a multi-step wizard component that collects user input progressively. State management is primarily handled through React Query for server communication and local React state for form data.

## Backend Architecture
- **Framework**: Express.js with TypeScript
- **Database**: PostgreSQL with Drizzle ORM for type-safe database operations
- **Storage Strategy**: Dual storage implementation with in-memory storage for development and PostgreSQL for production
- **API Design**: RESTful API with structured JSON responses
- **Error Handling**: Centralized error handling middleware with proper HTTP status codes

The backend uses a service-oriented architecture with clear separation between routes, storage layer, and business logic. The storage interface pattern allows for easy switching between different storage implementations.

## Data Storage Solutions
- **Database**: PostgreSQL as the primary database
- **ORM**: Drizzle ORM with schema-first approach and automatic TypeScript type generation
- **Schema Design**: Separate tables for users and formulations with proper relationships
- **Migration Strategy**: Drizzle Kit for database schema migrations

The database schema supports user management and formulation storage with JSON fields for ingredient arrays, allowing flexible storage of variable ingredient lists while maintaining relational integrity.

## Authentication and Authorization
The current implementation uses a basic storage interface for user management but does not implement active authentication middleware. The system is designed to support user-based data isolation through email-based queries.

# External Dependencies

## Third-party Services
- **OpenAI API**: Integration with GPT-5 for generating personalized skincare formulation recommendations
- **Neon Database**: Serverless PostgreSQL database hosting for production deployments

## Development Tools
- **Replit**: Development environment integration with custom Vite plugins
- **TypeScript**: Full-stack type safety with shared schema definitions
- **ESBuild**: Production build optimization for server-side code

## UI and Styling Dependencies
- **Radix UI**: Comprehensive set of accessible, unstyled UI primitives
- **Tailwind CSS**: Utility-first CSS framework with custom design system
- **Lucide React**: Icon library for consistent iconography
- **jsPDF**: Client-side PDF generation for formulation reports

## Database and Storage
- **Drizzle ORM**: Type-safe database operations with PostgreSQL
- **Zod**: Runtime type validation and schema definition
- **Connect PG Simple**: PostgreSQL session store for potential session management

The application architecture emphasizes type safety throughout the stack, with shared TypeScript definitions between frontend and backend. The modular design allows for easy extension of features and integration of additional services.