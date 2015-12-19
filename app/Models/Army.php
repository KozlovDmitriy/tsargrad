<?php
/**
 * Created by PhpStorm.
 * User: �����
 * Date: 09.11.2015
 * Time: 16:24
 */

namespace App\Models;

use App\Exceptions\GameException;
use App\Facades\GameField;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Army extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'armies';

    protected $dates = ['deleted_at', 'updated_at', 'created_at'];

    /**
     * Формула расчета стоимости покупки воинов в армию.
     *
     * @param int $level уровень
     * @param int $count количество воинов для покупки
     * @return int
     */
    private static function formulaBuy($level, $count)
    {
        return intval(exp($level / 10) * 2 * $count);
    }

    /**
     * Формула расчета стоимости апгрейда армии...
     *
     * @param int $level уровень
     * @param int $strength количество воинов в армии на данный момент
     * @return int
     */
    private static function formulaUpgrade($level, $strength)
    {
        return intval(exp($level / 10) * 15 * ($strength + 1));
    }

    /**
     * Get all squads.
     * One to Many relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function squads()
    {
        return $this->hasMany('App\Models\Squad');
    }

    /**
     * Get a castle.
     * One to One relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function castle()
    {
        return $this->belongsTo('App\Models\Castle');
    }

    /**
     * Сбросить армию по-умолчанию
     *
     * @return Army
     * @throws \Exception
     */
    public function reset()
    {
        //$this->level = 1; // Уровень не сбрасывать?
        $this->size = 0;

        DB::beginTransaction();
        try {
            // Сохранить армию...
            //$this->squads()->delete(); // отряды не удалять?
            $this->save();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw($ex); // next...
        }
        DB::commit();

        return $this;
    }

    /**
     * Совершить поход на вражеский замок.
     *
     * @param {string} $name имя отряда
     * @param {int} $count количество в отряде
     * @param Castle $goal замок
     * @return Squad
     * @throws GameException
     * @throws \Exception
     */
    public function crusade($name, $count, Castle $goal)
    {
        // Есть ли возможность создать отряд?
        if ($this->size - $count < 0) {
            throw new GameException('Нельзя создать отряд для похода. Не хватает храбрых воинов.');
        }

        $squad = new Squad(['name' => $name, 'size' => $count]);
        $squad->crusade_at = Carbon::now(); // Начало похода
        // Время на поход...
        $minutes = GameField::howMuchTime($this->castle, $goal);
        $squad->battle_at = Carbon::now()->addMinutes($minutes); // Конец похода

        DB::beginTransaction();
        try {
            $squad->goal()->associate($goal); // Вражеский замок
            // Сохранить отряд...
            $this->squads()->save($squad);
        } catch (\Exception $ex) {
            DB::rollBack();
            throw($ex); // next...
        }
        DB::commit();

        return $squad;
    }

    /**
     * Купить новых воинов в армию замка.
     *
     * @param int $count кол-во воинов для покупки
     * @return bool
     * @throws GameException
     * @throws \Exception
     */
    public function buy($count)
    {
        if (!(is_integer($count) && $count > 0)) {
            return false;
        }

        $castle = $this->castle;
        // Стоимость покупки воинов...
        $cost = static::formulaBuy($this->level, $count);
        // Количество еды и дерева в замке...
        $wood = $castle->getResources('wood');
        $food = $castle->getResources('food');

        // Хватает ресурсов?
        if ($wood < $cost || $food < $cost) {
            $mw = "ДЕРЕВО ({$wood} / {$cost})";
            $mf = "ЕДЫ ({$food} / {$cost})";
            $des = $wood < $cost && $food < $cost ? $mw . ' и ' . $mf : $wood < $cost ? $mw : $mf;
            throw new GameException("Нельзя купить новых воинов. Не хватает $des");
        }

        // Собственно покупка...
        DB::beginTransaction();
        try {
            $castle->subResource('wood', $cost);
            $castle->subResource('food', $cost);
            $this->size += $count;
            $this->save();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw($ex); // next...
        }
        DB::commit();

        return true;
    }

    /**
     * Улучшить уровень армии замка.
     *
     * @param int $addнLevel на сколько увеличить уровней сразу?
     * @return bool
     * @throws GameException
     * @throws \Exception
     */
    public function upgrade($addLevel = 1)
    {
        if (!(is_integer($addLevel) && $addLevel > 1)) {
            return false;
        }
        $castle = $this->castle;

        // Стоимость апргрейда на указанное число уровней...
        $cost = static::formulaUpgrade($this->level, $this->strength);
        $level = $this->level + 1; // следующий уровень...
        while ($level < $this->level + $addLevel) {
            $cost += static::formulaUpgrade($level++, $this->strength);
        }

        // Количество золота в замке...
        $gold = $castle->getResources('gold');
        // Хватает ресурсов?
        if ($gold < $cost) {
            throw new GameException("Нельзя улучшить армию. Не хватает ЗОЛОТА ($gold / $cost)");
        }

        // Собственно покупка...
        DB::beginTransaction();
        try {
            $castle->subResource('gold', $cost);
            $this->level += $addLevel;
            $this->save();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw($ex); // next...
        }
        DB::commit();

        return true;
    }

    /**
     * Получить состояния всех отрядов армии.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSquadsStates()
    {
        $arr = [];
        foreach ($this->squads()->getResults() as $s) {
            $arr[$s->id] = $s->hstate;
        }
        return collect($arr);
    }

    /**
     * Получить размер каждого отряда.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSquadsSize()
    {
        $arr = [];
        foreach ($this->squads()->getResults() as $s) {
            $arr[$s->id] = $s->size;
        }
        return collect($arr);
    }

    /**
     * Magic getter!
     *
     * @param string $key
     * @return int|mixed
     */
    public function __get($key)
    {
        // Получить размер всех отрядов армии...
        if ($key == 'sizeOfSquads') {
            $size = 0;
            foreach ($this->squads()->getResults() as $s) {
                $size += $s->size;
            }
            return $size;
        }

        // Получить силу армии...
        if ($key == 'strength') {
            return $this->size + $this->sizeOfSquads;
        }

        return parent::__get($key);
    }
}