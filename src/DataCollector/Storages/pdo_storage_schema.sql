CREATE TABLE data_collector (
    id varchar(128) PRIMARY KEY,
    data TEXT,
    meta_utime varchar(32),
    meta_datetime varchar(32),
    meta_uri varchar(1024),
    meta_ip varchar(32),
    meta_method varchar(16)
);

CREATE INDEX idx_data_collector_id ON data_collector (id);
CREATE INDEX idx_data_collector_meta_utime ON data_collector (meta_utime);
CREATE INDEX idx_data_collector_meta_datetime ON data_collector (meta_datetime);
CREATE INDEX idx_data_collector_meta_ip ON data_collector (meta_ip);
CREATE INDEX idx_data_collector_meta_method ON data_collector (meta_method);
