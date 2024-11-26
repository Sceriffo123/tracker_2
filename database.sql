-- Aggiornamento tabella autorizzazioni_titolarita
ALTER TABLE autorizzazioni_titolarita
ADD COLUMN sede_operativa text DEFAULT NULL AFTER indirizzo,
ADD COLUMN rimessa_comunale text DEFAULT NULL AFTER sede_operativa,
ADD COLUMN rimessa_provinciale text DEFAULT NULL AFTER rimessa_comunale,
ADD COLUMN tipo_ottenimento enum('assegnazione','trasferimento') NOT NULL DEFAULT 'assegnazione' AFTER tipo_titolare;

-- Creazione tabella autorizzazioni_autisti
CREATE TABLE IF NOT EXISTS autorizzazioni_autisti (
    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    autorizzazione_titolarita_id int(11) NOT NULL,
    nome varchar(100) NOT NULL,
    cognome varchar(100) NOT NULL,
    codice_fiscale varchar(16) DEFAULT NULL,
    data_inizio datetime NOT NULL,
    data_fine datetime DEFAULT NULL,
    created_by int(11) NOT NULL,
    created_at datetime DEFAULT current_timestamp(),
    INDEX (autorizzazione_titolarita_id),
    FOREIGN KEY (autorizzazione_titolarita_id) 
        REFERENCES autorizzazioni_titolarita(id) 
        ON DELETE CASCADE
);