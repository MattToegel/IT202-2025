ALTER TABLE `IT202-M25-Stocks`
ADD CONSTRAINT uq_last_trading_day_symbol
UNIQUE (`last_trading_day`, `symbol`);