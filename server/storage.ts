import { type User, type InsertUser, type Formulation, type InsertFormulation } from "@shared/schema";
import { randomUUID } from "crypto";

export interface IStorage {
  getUser(id: string): Promise<User | undefined>;
  getUserByUsername(username: string): Promise<User | undefined>;
  createUser(user: InsertUser): Promise<User>;
  createFormulation(formulation: InsertFormulation): Promise<Formulation>;
  getFormulation(id: string): Promise<Formulation | undefined>;
  getFormulationsByEmail(email: string): Promise<Formulation[]>;
}

export class MemStorage implements IStorage {
  private users: Map<string, User>;
  private formulations: Map<string, Formulation>;

  constructor() {
    this.users = new Map();
    this.formulations = new Map();
  }

  async getUser(id: string): Promise<User | undefined> {
    return this.users.get(id);
  }

  async getUserByUsername(username: string): Promise<User | undefined> {
    return Array.from(this.users.values()).find(
      (user) => user.username === username,
    );
  }

  async createUser(insertUser: InsertUser): Promise<User> {
    const id = randomUUID();
    const user: User = { ...insertUser, id };
    this.users.set(id, user);
    return user;
  }

  async createFormulation(insertFormulation: InsertFormulation): Promise<Formulation> {
    const id = randomUUID();
    const formulation: Formulation = {
      ...insertFormulation,
      id,
      createdAt: new Date(),
    };
    this.formulations.set(id, formulation);
    return formulation;
  }

  async getFormulation(id: string): Promise<Formulation | undefined> {
    return this.formulations.get(id);
  }

  async getFormulationsByEmail(email: string): Promise<Formulation[]> {
    return Array.from(this.formulations.values()).filter(
      (formulation) => formulation.email === email,
    );
  }
}

export const storage = new MemStorage();
