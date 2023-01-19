export enum NotificationType {
    INFO = "info",
    ERROR = "error"
}

export interface NotificationButtonOpt {
    libelle: string
    callback: Function
}

export interface Notification {
    key: number
    type: NotificationType
    libelle: string
    delay: number
    callback?: Function
    callbackDone: boolean
    button?: NotificationButtonOpt
    hide: boolean
}

export enum NotificationDelay {
    NONE = 0,
    SHORT = 3000,
    MEDIUM = 4000,
    LONG = 10000
}

export interface NotificationOpt {
    delay?: NotificationDelay
    callback?: Function
    button?: NotificationButtonOpt
}
