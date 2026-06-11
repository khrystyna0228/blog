<?php

namespace App\Jobs\GenerateCatalog;

class GenerateCatalogMainJob extends AbstractJob
{
    public function handle()
    {
        $this->debug('start');

        GenerateCatalogCacheJob::dispatchSync();

        $chainPrices = $this->getChainPrices();

        $chainMain = [
            new GenerateCategoriesJob,
            new GenerateDeliveriesJob,
            new GeneratePointsJob,
        ];

        $chainLast = [
            new ArchiveUploadsJob,
            new SendPriceRequestJob,
        ];

        $chain = array_merge($chainPrices, $chainMain, $chainLast);

        GenerateGoodsFileJob::withChain($chain)->dispatch();

        $this->debug('finish');
    }

    private function getChainPrices()
    {
        $result = [];
        $products = collect([1, 2, 3, 4, 5]);
        $fileNum = 1;

        foreach ($products->chunk(1) as $chunk) {
            $result[] = new GeneratePricesFileChunkJob($chunk, $fileNum);
            $fileNum++;
        }

        return $result;
    }
}
