<?php

class MultiCurrencyAccount {
    private $balances = [];
    private $exchangeRates = [];
    private $enabledCurrencies = [];

    public function __construct($exchangeRates) {
        $this->exchangeRates = $exchangeRates;
        $this->enabledCurrencies = array_keys($exchangeRates);
    }

    public function deposit($amount, $currency) {
        $this->checkCurrencyEnabled($currency);
        if (isset($this->balances[$currency])) {
            $this->balances[$currency] += $amount;
        } else {
            $this->balances[$currency] = $amount;
        }
    }

    public function withdraw($amount, $currency) {
        $this->checkCurrencyEnabled($currency);
        if (!isset($this->balances[$currency]) || $this->balances[$currency] < $amount) {
            throw new Exception("Недостаточно средств на счете в указанной валюте");
        }
        $this->balances[$currency] -= $amount;
    }

    public function convert($amount, $fromCurrency, $toCurrency) {
        $this->checkCurrencyEnabled($fromCurrency);
        $this->checkCurrencyEnabled($toCurrency);
        if (!isset($this->exchangeRates[$fromCurrency]) || !isset($this->exchangeRates[$toCurrency])) {
            throw new Exception("Обменный курс для одной из валют не найден");
        }


        $baseAmount = $amount / $this->exchangeRates[$fromCurrency];
        $convertedAmount = $baseAmount * $this->exchangeRates[$toCurrency];

        return $convertedAmount;
    }

    public function getBalance($currency) {
        $this->checkCurrencyEnabled($currency);
        return isset($this->balances[$currency]) ? $this->balances[$currency] : 0.0;
    }

    public function getTotalBalanceInCurrency($targetCurrency) {
        $this->checkCurrencyEnabled($targetCurrency);
        if (!isset($this->exchangeRates[$targetCurrency])) {
            throw new Exception("Обменный курс для целевой валюты не найден");
        }

        $totalBalance = 0.0;
        foreach ($this->balances as $currency => $balance) {
            if ($this->isCurrencyEnabled($currency)) {
                $totalBalance += $this->convert($balance, $currency, $targetCurrency);
            }
        }

        return $totalBalance;
    }

    public function enableCurrency($currency) {
        if (!in_array($currency, $this->enabledCurrencies)) {
            $this->enabledCurrencies[] = $currency;
        }
    }

    public function disableCurrency($currency, $targetCurrency) {
        if (($key = array_search($currency, $this->enabledCurrencies)) !== false) {
            if (!in_array($targetCurrency, $this->enabledCurrencies)) {
                throw new Exception("Целевая валюта отключена");
            }
            $amount = $this->getBalance($currency);
            $convertedAmount = $this->convert($amount, $currency, $targetCurrency);
            $this->deposit($convertedAmount, $targetCurrency);
            $this->balances[$currency] = 0;

            unset($this->enabledCurrencies[$key]);
        }
    }
    public function getEnabledCurrencies() {
        return $this->enabledCurrencies;
    }

    private function isCurrencyEnabled($currency) {
        return in_array($currency, $this->enabledCurrencies);
    }


    private function checkCurrencyEnabled($currency) {
        if (!$this->isCurrencyEnabled($currency)) {
            throw new Exception("Операции с указанной валютой отключены");
        }
    }
}



    //1.
$exchangeRates = [
    'USD' => 0.014,
    'EUR' => 0.0125,
    'RUB' => 1];
$account = new MultiCurrencyAccount($exchangeRates);
$account->deposit(40, 'USD');
$account->deposit(50, 'EUR');
$account->deposit(7500, 'RUB');
    //2.
$account->getTotalBalanceInCurrency('USD');
$account->getTotalBalanceInCurrency('EUR');
$account->getTotalBalanceInCurrency('RUB');
    //3.
$account->deposit(1000, 'RUB');
$account->deposit(50, 'EUR');
$account->withdraw(10,'USD');
    //4.
$exchangeRates = [
    'USD' => 0.01,
    'EUR' => 0.0066,
    'RUB' => 1];
    //5.
$account->getTotalBalanceInCurrency('RUB');
    //6.
$exchangeRates = [
    'USD' => 0.66,
    'EUR' => 1,
    'RUB' => 150];
$account->getTotalBalanceInCurrency('EUR');
    //7.
$money = $account->withdraw(1000, 'RUB');
$account->deposit($money, 'EUR');
    //8.
$exchangeRates = [
    'USD' => 0.83,
    'EUR' => 1,
    'RUB' => 120];
    //9.
$account->getTotalBalanceInCurrency('EUR');
    //10.
$exchangeRates = [
    'USD' => 100,
    'EUR' => 120,
    'RUB' => 1];
$account->disableCurrency('EUR','RUB');
$account->disableCurrency('USD', 'RUB');
echo "Доступные валюты: " . implode(', ', $account->getEnabledCurrencies()) . "<br>";
$account->getTotalBalanceInCurrency('RUB');

?>
