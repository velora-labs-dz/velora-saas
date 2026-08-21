# VELORA — DEVOPS & OPERATIONS

## 1. Current local environment

Current reality:

- Windows
- Laravel Herd
- native PostgreSQL
- pgAdmin
- no Docker
- Redis not currently configured

Do not document Docker/Redis as active infrastructure until they are actually installed and used.

## 2. Future environments

Minimum target:

- local
- staging
- production

Production must never be the place where migrations are experimentally developed.

## 3. Backups

Database backup requirements:

- automated backups
- retention
- restore procedure
- restore testing

Object storage requires separate backup thinking.

## 4. Deployment

Deployment must eventually include:

1. build
2. tests
3. migration review
4. backup
5. deploy
6. migrate
7. health check
8. rollback path

## 5. Environment variables

`.env.example` contains safe placeholders.

Never commit secrets.

## 6. Queues

When Redis is introduced:

- configure workers
- monitor failed jobs
- retry safely
- make jobs idempotent where needed

## 7. Scheduler

When automation requires scheduled jobs:

- membership expiry
- reminders
- subscription transitions
- reports

use Laravel's scheduler and monitored workers.

## 8. Monitoring

Production eventually needs:

- uptime
- error monitoring
- queue health
- database health
- slow query visibility
- application logs

## 9. Disaster recovery

Define:

- RPO
- RTO
- backup frequency
- restore procedure
- incident owner

## 10. Migration safety

Prefer additive migrations.

Never casually:

- drop columns
- rename columns
- delete data
- rewrite large datasets

without a tested migration path.
