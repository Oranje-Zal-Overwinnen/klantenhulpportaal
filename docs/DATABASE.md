# Database

| Entiteit | Tabel | Doel | Issues |
| --- | --- | --- | --- |
| Gebruiker | `users` | Accounts, rollen en contactgegevens | #5–#11, #16 |
| Ticket | `tickets` | Vragen/problemen met status, maker en toewijzing | #12–#17, #22–#28 |
| Categorie | `categories` | Door administrators beheerde categorieën | #13, #18–#21 |
| Categorie-ticket | `category_ticket` | Koppeltabel voor de N:M-relatie | #13, #19–#21 |
| Reactie | `replies` | Antwoorden op een ticket | #22–#24 |
| Notitie | `notes` | Interne notities voor administrators | #25–#28 |

```mermaid
erDiagram
    direction LR
    USER ||--o{ TICKET : "maakt"
    USER |o--o{ TICKET : "is toegewezen aan"
    USER ||--o{ REPLY : "schrijft"
    USER ||--o{ NOTE : "schrijft"
    TICKET ||--o{ REPLY : "bevat"
    TICKET ||--o{ NOTE : "bevat"
    TICKET ||--o{ CATEGORY_TICKET : "heeft"
    CATEGORY ||--o{ CATEGORY_TICKET : "heeft"

    USER {
        bigint id PK
        string first_name
        string last_name
        string email UK
        timestamp email_verified_at
        string password
        string role "user | admin"
        string phone_number
        string remember_token
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    TICKET {
        bigint id PK
        string title
        text description
        string status "pending | in_progress | resolved"
        bigint user_id FK
        bigint assigned_to FK
        timestamp created_at
        timestamp updated_at
    }

    CATEGORY {
        bigint id PK
        string name UK
        timestamp created_at
        timestamp updated_at
    }

    REPLY {
        bigint id PK
        bigint ticket_id FK
        bigint user_id FK
        text body
        timestamp created_at
        timestamp updated_at
    }

    NOTE {
        bigint id PK
        bigint ticket_id FK
        bigint user_id FK
        text body
        timestamp created_at
        timestamp updated_at
    }

    CATEGORY_TICKET {
        bigint category_id PK, FK
        bigint ticket_id PK, FK
    }
```

- `||` = precies één
- `|o` = nul of één
- `o{` = nul of meer
