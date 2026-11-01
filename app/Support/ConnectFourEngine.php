<?php

namespace App\Support;

/**
 * Board rules and the AI for the "Vier op een rij" game against OOHPA Arjan.
 *
 * The server owns the board. A client cannot choose the result, because the server
 * checks every move and decides every reply. The AI wins when it can, blocks an
 * immediate player win, and otherwise searches with minimax.
 */
final class ConnectFourEngine
{
    public const ROWS = 6;

    public const COLUMNS = 7;

    public const EMPTY = 0;

    public const PLAYER = 1;

    public const ARJAN = 2;

    /**
     * Search depth for the minimax lookup. Deeper search costs more time per move.
     */
    public const SEARCH_DEPTH = 4;

    /**
     * Column order for the search. The center column comes first, so equal scores prefer the center.
     */
    private const COLUMN_ORDER = [3, 2, 4, 1, 5, 0, 6];

    /**
     * Base score for a win. The search depth is added, so a faster win scores higher.
     */
    private const WIN_SCORE = 1000;

    private const CENTER_COLUMN = 3;

    private const CENTER_WEIGHT = 4;

    private const WINNING_LENGTH = 4;

    /**
     * Direction pairs for win detection. Each pair holds one direction and its opposite.
     */
    private const DIRECTION_PAIRS = [
        [[0, 1], [0, -1]],
        [[1, 0], [-1, 0]],
        [[1, 1], [-1, -1]],
        [[1, -1], [-1, 1]],
    ];

    /**
     * @return array<int, array<int, int>>
     */
    public static function emptyBoard(): array
    {
        return array_fill(0, self::ROWS, array_fill(0, self::COLUMNS, self::EMPTY));
    }

    /**
     * Return the lowest empty row in a column, or null when the column is full.
     *
     * @param  array<int, array<int, int>>  $board
     */
    public static function lowestEmptyRow(array $board, int $column): ?int
    {
        for ($row = self::ROWS - 1; $row >= 0; $row--) {
            if ($board[$row][$column] === self::EMPTY) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Place a token in a column and return its row, or null when the column is full.
     *
     * @param  array<int, array<int, int>>  $board
     */
    public static function dropToken(array &$board, int $column, int $player): ?int
    {
        $row = self::lowestEmptyRow($board, $column);
        if ($row === null) {
            return null;
        }

        $board[$row][$column] = $player;

        return $row;
    }

    /**
     * Return the cells of a four-in-a-row through the given cell, or null when there is none.
     *
     * @param  array<int, array<int, int>>  $board
     * @return array<int, array{0: int, 1: int}>|null
     */
    public static function winningCells(array $board, int $row, int $column, int $player): ?array
    {
        foreach (self::DIRECTION_PAIRS as [$forward, $backward]) {
            $cells = [[$row, $column]];

            foreach ([$forward, $backward] as [$deltaRow, $deltaColumn]) {
                $checkRow = $row + $deltaRow;
                $checkColumn = $column + $deltaColumn;

                while (self::isInside($checkRow, $checkColumn) && $board[$checkRow][$checkColumn] === $player) {
                    $cells[] = [$checkRow, $checkColumn];
                    $checkRow += $deltaRow;
                    $checkColumn += $deltaColumn;
                }
            }

            if (count($cells) >= self::WINNING_LENGTH) {
                return $cells;
            }
        }

        return null;
    }

    /**
     * @param  array<int, array<int, int>>  $board
     */
    public static function isFull(array $board): bool
    {
        foreach ($board[0] as $cell) {
            if ($cell === self::EMPTY) {
                return false;
            }
        }

        return true;
    }

    /**
     * Choose the column that Arjan plays. The same board always gives the same column.
     *
     * @param  array<int, array<int, int>>  $board
     */
    public static function chooseArjanMove(array $board): int
    {
        $winningColumn = self::findCompletingColumn($board, self::ARJAN);
        if ($winningColumn !== null) {
            return $winningColumn;
        }

        // Arjan blocks first, because a missed block loses the match on the next move.
        $blockingColumn = self::findCompletingColumn($board, self::PLAYER);
        if ($blockingColumn !== null) {
            return $blockingColumn;
        }

        return self::bestMinimaxColumn($board, self::SEARCH_DEPTH) ?? self::validColumns($board)[0];
    }

    /**
     * Return the first column where a token of this player wins at once, or null.
     *
     * @param  array<int, array<int, int>>  $board
     */
    private static function findCompletingColumn(array $board, int $player): ?int
    {
        foreach (range(0, self::COLUMNS - 1) as $column) {
            $row = self::lowestEmptyRow($board, $column);
            if ($row === null) {
                continue;
            }

            $trial = $board;
            $trial[$row][$column] = $player;
            if (self::winningCells($trial, $row, $column, $player) !== null) {
                return $column;
            }
        }

        return null;
    }

    /**
     * @param  array<int, array<int, int>>  $board
     */
    private static function bestMinimaxColumn(array $board, int $depth): ?int
    {
        $bestScore = -INF;
        $bestColumn = null;

        foreach (self::validColumns($board) as $column) {
            $row = self::lowestEmptyRow($board, $column);
            $trial = $board;
            $trial[$row][$column] = self::ARJAN;

            $score = self::minimax($trial, $depth - 1, false, -INF, INF);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestColumn = $column;
            }
        }

        return $bestColumn;
    }

    /**
     * @param  array<int, array<int, int>>  $board
     */
    private static function minimax(array $board, int $depth, bool $isMaximizing, float $alpha, float $beta): float|int
    {
        if ($depth === 0) {
            return self::evaluateBoard($board);
        }

        $validColumns = self::validColumns($board);
        if ($validColumns === []) {
            return 0;
        }

        return $isMaximizing
            ? self::maximize($board, $depth, $validColumns, $alpha, $beta)
            : self::minimize($board, $depth, $validColumns, $alpha, $beta);
    }

    /**
     * @param  array<int, array<int, int>>  $board
     * @param  array<int, int>  $validColumns
     */
    private static function maximize(array $board, int $depth, array $validColumns, float $alpha, float $beta): float|int
    {
        $bestEvaluation = -INF;

        foreach ($validColumns as $column) {
            $row = self::lowestEmptyRow($board, $column);
            $trial = $board;
            $trial[$row][$column] = self::ARJAN;

            if (self::winningCells($trial, $row, $column, self::ARJAN) !== null) {
                return self::WIN_SCORE + $depth;
            }

            $evaluation = self::minimax($trial, $depth - 1, false, $alpha, $beta);
            $bestEvaluation = max($bestEvaluation, $evaluation);
            $alpha = max($alpha, $evaluation);
            if ($beta <= $alpha) {
                break;
            }
        }

        return $bestEvaluation;
    }

    /**
     * @param  array<int, array<int, int>>  $board
     * @param  array<int, int>  $validColumns
     */
    private static function minimize(array $board, int $depth, array $validColumns, float $alpha, float $beta): float|int
    {
        $bestEvaluation = INF;

        foreach ($validColumns as $column) {
            $row = self::lowestEmptyRow($board, $column);
            $trial = $board;
            $trial[$row][$column] = self::PLAYER;

            if (self::winningCells($trial, $row, $column, self::PLAYER) !== null) {
                return -self::WIN_SCORE - $depth;
            }

            $evaluation = self::minimax($trial, $depth - 1, true, $alpha, $beta);
            $bestEvaluation = min($bestEvaluation, $evaluation);
            $beta = min($beta, $evaluation);
            if ($beta <= $alpha) {
                break;
            }
        }

        return $bestEvaluation;
    }

    /**
     * Score the board by the center column only. This keeps the search fast and matches the earlier browser logic.
     *
     * @param  array<int, array<int, int>>  $board
     */
    private static function evaluateBoard(array $board): int
    {
        $score = 0;
        for ($row = 0; $row < self::ROWS; $row++) {
            if ($board[$row][self::CENTER_COLUMN] === self::ARJAN) {
                $score += self::CENTER_WEIGHT;
            }
            if ($board[$row][self::CENTER_COLUMN] === self::PLAYER) {
                $score -= self::CENTER_WEIGHT;
            }
        }

        return $score;
    }

    /**
     * @param  array<int, array<int, int>>  $board
     * @return array<int, int>
     */
    private static function validColumns(array $board): array
    {
        return array_values(array_filter(
            self::COLUMN_ORDER,
            fn (int $column): bool => self::lowestEmptyRow($board, $column) !== null
        ));
    }

    private static function isInside(int $row, int $column): bool
    {
        return $row >= 0 && $row < self::ROWS && $column >= 0 && $column < self::COLUMNS;
    }
}
