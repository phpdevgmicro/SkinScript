import { sql } from "drizzle-orm";
import { pgTable, text, varchar, jsonb, timestamp, integer } from "drizzle-orm/pg-core";
import { createInsertSchema } from "drizzle-zod";
import { z } from "zod";

export const users = pgTable("users", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  username: text("username").notNull().unique(),
  password: text("password").notNull(),
});

export const formulations = pgTable("formulations", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  skinType: text("skin_type").notNull(),
  format: text("format").notNull(),
  actives: jsonb("actives").$type<string[]>().notNull().default([]),
  extracts: jsonb("extracts").$type<string[]>().notNull().default([]),
  hydrators: jsonb("hydrators").$type<string[]>().notNull().default([]),
  firstName: text("first_name").notNull(),
  lastName: text("last_name").notNull(),
  email: text("email").notNull(),
  skinConcerns: text("skin_concerns"),
  newsletter: integer("newsletter").default(0),
  safetyScore: integer("safety_score").notNull(),
  aiSuggestion: text("ai_suggestion"),
  createdAt: timestamp("created_at").defaultNow().notNull(),
});

export const insertUserSchema = createInsertSchema(users).pick({
  username: true,
  password: true,
});

export const insertFormulationSchema = createInsertSchema(formulations).omit({
  id: true,
  createdAt: true,
}).extend({
  newsletter: z.boolean().transform(val => val ? 1 : 0),
});

export type InsertUser = z.infer<typeof insertUserSchema>;
export type User = typeof users.$inferSelect;
export type InsertFormulation = z.infer<typeof insertFormulationSchema>;
export type Formulation = typeof formulations.$inferSelect;
