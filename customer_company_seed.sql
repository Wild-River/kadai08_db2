-- customers.company が全件NULLだったため、ダミーの会社名/部署名を補完する

USE bean_trade;

UPDATE customers SET company = '有限会社ヤマダコーヒー' WHERE name = '山田珈琲店';
UPDATE customers SET company = '佐藤商事株式会社'       WHERE name = 'カフェさとう';
UPDATE customers SET company = '総務部'                 WHERE name = '株式会社ライトハウス';
UPDATE customers SET company = '購買部'                 WHERE name = '合同会社ノースウインド';
UPDATE customers SET company = '仕入部'                 WHERE name = '有限会社サンライズ商事';
UPDATE customers SET company = 'フリーランス'           WHERE name = '田中デザイン事務所';
UPDATE customers SET company = 'テスト株式会社'         WHERE name = 'test';
