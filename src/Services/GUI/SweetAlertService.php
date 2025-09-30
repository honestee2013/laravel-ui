<?php
namespace QuickerFaster\LaravelUI\Services\GUI;



class SweetAlertService
{
    public static function showAlert($component, $title, $message, $type = 'success', $icon = 'success', $confirmButtonText = 'OK')
    {
        $data = [
            'title' => $title,
            'text' => $message,
            'type' => $type,
            'icon' => $icon,
            'confirmButtonText' => $confirmButtonText,
        ];


        // Display saving success message
        $component->dispatch('swal:'.$type, $data);

    }


    public static function showError($component, $title, $message)
    {
        self::showAlert($component, $title, $message, 'error', 'error');
    }


    public static function showSuccess($component, $title, $message)
    {
        self::showAlert($component, $title, $message, 'success', 'success');
    }


    public static function showWarning($component, $title, $message)
    {
        self::showAlert($component, $title, $message, 'warning', 'warning');
    }


    public static function showInfo($component, $title, $message)
    {
        self::showAlert($component, $title, $message, 'info', 'info');
    }


    /*public static function showConfirmation($component, $title, $message, $confirmButtonText = 'OK', $cancelButtonText = 'Cancel')
    {
        $data = [
            'title' => $title,
            'text' => $message,
            'icon' => 'warning',
            'showCancelButton' => true,
            'confirmButtonText' => $confirmButtonText,
            'cancelButtonText' => $cancelButtonText,
        ];

        // Display confirmation message
        $component->dispatch('swal:confirmation', $data);
    }


    public static function showPrompt($component, $title, $message, $inputType = 'text', $confirmButtonText = 'OK', $cancelButtonText = 'Cancel')
    {
        $data = [
            'title' => $title,
            'text' => $message,
            'icon' => 'question',
            'input' => $inputType,
            'showCancelButton' => true,
            'confirmButtonText' => $confirmButtonText,
            'cancelButtonText' => $cancelButtonText,
        ];

        // Display prompt message
        $component->dispatch('swal:prompt', $data);
    }


    public static function showToast($component, $title, $message, $icon = 'success', $position = 'top-end', $timer = 3000)
    {
        $data = [
            'title' => $title,
            'text' => $message,
            'icon' => $icon,
            'position' => $position,
            'timer' => $timer,
        ];

        // Display toast message
        $component->dispatch('swal:toast', $data);
    }


    public static function showLoading($component, $title, $message)
    {
        $data = [
            'title' => $title,
            'text' => $message,
            'icon' => 'info',
            'showLoaderOnConfirm' => true,
        ];

        // Display loading message
        $component->dispatch('swal:loading', $data);
    }*/







}
