<?php
declare(strict_types=1);

namespace CocoCore\Modules\Ranking;

/** Classement : à égalité de pourcentage, même rang ; le suivant saute (1, 2, 2, 4). */
final class RankingService
{
    /**
     * @param array<int|string,float> $percents id => pourcentage
     * @return array<int|string,int> id => rang
     */
    public function rank(array $percents): array
    {
        arsort($percents, SORT_NUMERIC);
        $ranks = [];
        $position = 0;
        $prev = null;
        $rank = 0;
        foreach ($percents as $id => $p) {
            $position++;
            if ($prev === null || abs($p - $prev) > 0.00001) {
                $rank = $position;
            }
            $ranks[$id] = $rank;
            $prev = $p;
        }
        return $ranks;
    }

    /** 1 → « 1er », 2 → « 2ème ». */
    public function ordinal(int $rank): string
    {
        return $rank === 1 ? '1er' : $rank . 'ème';
    }
}
