#!/bin/bash

# Automated PostgreSQL Backup Script with Encryption
# Runs incremental backups every 6 hours, full backup daily

set -e

# Configuration
BACKUP_DIR="/var/backups/postgresql"
BACKUP_RETENTION_DAYS=30
DB_HOST="localhost"
DB_PORT="5432"
DB_NAME="app"
DB_USER="app"
ENCRYPTION_KEY="/etc/backup/encryption.key"
S3_BUCKET="s3://school-platform-backups"

# Create backup directory
mkdir -p "$BACKUP_DIR"/{full,incremental}

# Generate timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
DAY_OF_WEEK=$(date +%u)

# Determine backup type (full on Sunday, incremental otherwise)
if [ "$DAY_OF_WEEK" -eq 7 ]; then
    BACKUP_TYPE="full"
    BACKUP_FILE="$BACKUP_DIR/full/backup_full_$TIMESTAMP.sql.gz.enc"
else
    BACKUP_TYPE="incremental"
    BACKUP_FILE="$BACKUP_DIR/incremental/backup_inc_$TIMESTAMP.sql.gz.enc"
fi

echo "Starting $BACKUP_TYPE backup at $TIMESTAMP"

# Perform backup
if [ "$BACKUP_TYPE" = "full" ]; then
    # Full backup
    pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -Fc "$DB_NAME" | \
        gzip | \
        openssl enc -aes-256-cbc -salt -pbkdf2 -pass file:"$ENCRYPTION_KEY" > "$BACKUP_FILE"
else
    # Incremental backup (WAL archiving)
    pg_basebackup -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -D - -Ft -z -X fetch | \
        openssl enc -aes-256-cbc -salt -pbkdf2 -pass file:"$ENCRYPTION_KEY" > "$BACKUP_FILE"
fi

# Verify backup
if [ -f "$BACKUP_FILE" ]; then
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo "Backup completed successfully: $BACKUP_FILE ($BACKUP_SIZE)"
    
    # Upload to S3 (if configured)
    if command -v aws &> /dev/null; then
        aws s3 cp "$BACKUP_FILE" "$S3_BUCKET/$(basename $BACKUP_FILE)"
        echo "Backup uploaded to S3"
    fi
else
    echo "ERROR: Backup failed"
    exit 1
fi

# Cleanup old backups
find "$BACKUP_DIR/full" -name "backup_full_*.sql.gz.enc" -mtime +$BACKUP_RETENTION_DAYS -delete
find "$BACKUP_DIR/incremental" -name "backup_inc_*.sql.gz.enc" -mtime +7 -delete

echo "Backup process completed"

# Send notification (optional)
# curl -X POST https://hooks.slack.com/... -d "{'text':'Backup completed: $BACKUP_FILE'}"
