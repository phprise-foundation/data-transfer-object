# The OTAKU Manifesto: Fluid Structure Design

This philosophy is built upon the OTAKU acronym—reflecting a technical obsession with mastery, discipline, and structural purity.

## O - Own your Discipline (Be strict with yourself)
Restriction as Liberation: Quality is not an accident; it is the result of intentional constraints. We embrace restriction as the only path to liberation. To guarantee readability and prevent technical debt, the 9 rules of Object Calisthenics are mandatory:
1. Only one level of indentation per method.
2. Don’t use the ELSE keyword.
3. Wrap all primitives and Strings.
4. First class collections.
5. One dot per line.
6. Don’t abbreviate.
7. Keep all entities small (50 lines/10 files/package).
8. No classes with more than two instance variables.
9. No getters/setters/properties.

> The Rule: Write code that never needs "fixing" because it was built under absolute constraint.

## T - Tools for Composition (Compose like Unix)
The Rule of Modularity: Just like Unix tools, each component must do one thing and do it well. Our architecture is a set of small, replaceable parts connected by clear inputs and outputs.

> The Rule: Treat your application flow as a Data Pipe. Input enters through adapters, traverses the core, and exits, keeping logic isolated from side effects.

## A - Armor the Core (Protect the heart of the business)
Domain Sovereignty: The business logic is the most valuable asset. It must be protected from external changes. We use Clean Architecture to ensure that dependencies point only inwards, toward the Domain.

> The Rule: The "Heart" (Use Cases and Entities) must never know about frameworks, databases, or external APIs.

## K - Keep Infrastructure Silent (Infrastructure is just a detail)
Invisible Persistence: Databases, mailers, and message brokers are secondary details. We treat data access as an in-memory collection (Repository Pattern), abstracting SQL or external complexity.

> The Rule: Your domain entities should be POPOs (Plain Old PHP Objects), completely ignorant of how they are saved or transmitted.

## U - Universal Language & Contracts (Speak the user's language via clear contracts)
The Single Truth: We bridge the gap between business and code by using Ubiquitous Language (DDD). This language is solidified through Design-First Contracts (OpenAPI). The contract is the immutable technical promise that allows systems to communicate.

> The Rule: If the code doesn't read like the business expert speaks, or if the contract is bypassed, the system is broken.