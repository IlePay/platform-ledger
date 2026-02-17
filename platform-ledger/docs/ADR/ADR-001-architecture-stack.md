# ADR-001 : Architecture et Stack Technique

Date : 2026-02-03
Statut : Accepté
Auteur : Abolayo10

## Décision

Stack fintech avec séparation stricte :

- Ledger : Go (performance, typage fort)
- API : Laravel (productivité)
- Mobile : Flutter (single codebase)
- Database : PostgreSQL (ACID)
- Events : RabbitMQ

## Architecture

Flutter/Web → Laravel API → Go Ledger → PostgreSQL

## Justifications

Go pour Ledger :
- 10x plus rapide que PHP
- Typage strict = zéro erreur calcul
- Déploiement simple

PostgreSQL :
- SERIALIZABLE isolation
- JSONB flexible
- Point-in-time recovery

## Roadmap

MVP (6 mois) : Ledger + API + Mobile
Scale (12 mois) : Microservices + Kafka
