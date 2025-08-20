# SQL to add missing columns to the `procedures` table

```sql
ALTER TABLE procedures ADD COLUMN title VARCHAR(255);
ALTER TABLE procedures ADD COLUMN category VARCHAR(100);
ALTER TABLE procedures ADD COLUMN fee DECIMAL(10,2);
ALTER TABLE procedures ADD COLUMN treatment_area VARCHAR(100);
ALTER TABLE procedures ADD COLUMN status VARCHAR(50);
ALTER TABLE procedures ADD COLUMN created_at DATETIME NULL;
ALTER TABLE procedures ADD COLUMN updated_at DATETIME NULL;
ALTER TABLE procedures ADD COLUMN deleted_at DATETIME NULL;
```

> Run these statements in your database to ensure all required columns exist for the `procedures` table. If a column already exists, you may get an error for that line—just skip it and run the rest.
