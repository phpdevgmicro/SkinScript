import { type User, type InsertUser, type Formulation, type InsertFormulation, users, formulations } from "@shared/schema";
import { drizzle } from "drizzle-orm/postgres-js";
import { eq } from "drizzle-orm";
import postgres from "postgres";
import { randomUUID } from "crypto";

export interface IStorage {
  getUser(id: string): Promise<User | undefined>;
  getUserByUsername(username: string): Promise<User | undefined>;
  createUser(user: InsertUser): Promise<User>;
  createFormulation(formulation: InsertFormulation): Promise<Formulation>;
  getFormulation(id: string): Promise<Formulation | undefined>;
  getFormulationsByEmail(email: string): Promise<Formulation[]>;
}

// Initialize database connection
if (!process.env.DATABASE_URL) {
  throw new Error("DATABASE_URL environment variable is required");
}

const queryClient = postgres(process.env.DATABASE_URL!, {
  prepare: false,
  ssl: 'require',
  connection: {
    options: `--search_path=public`,
  }
});
const db = drizzle(queryClient);

export class DatabaseStorage implements IStorage {
  async getUser(id: string): Promise<User | undefined> {
    const result = await db.select().from(users).where(eq(users.id, id)).limit(1);
    return result[0];
  }

  async getUserByUsername(username: string): Promise<User | undefined> {
    const result = await db.select().from(users).where(eq(users.username, username)).limit(1);
    return result[0];
  }

  async createUser(insertUser: InsertUser): Promise<User> {
    const [result] = await db.insert(users).values(insertUser).returning();
    return result;
  }

  async createFormulation(insertFormulation: InsertFormulation): Promise<Formulation> {
    try {
      const [result] = await db.insert(formulations).values(insertFormulation).returning();
      return result;
    } catch (error) {
      console.error('Database insertion error:', error);
      throw error;
    }
  }

  async getFormulation(id: string): Promise<Formulation | undefined> {
    const result = await db.select().from(formulations).where(eq(formulations.id, id)).limit(1);
    return result[0];
  }

  async getFormulationsByEmail(email: string): Promise<Formulation[]> {
    const result = await db.select().from(formulations).where(eq(formulations.email, email));
    return result;
  }
}

export const storage = new DatabaseStorage();
