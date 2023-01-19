
export interface ApiData {
  type: string
  id: string
  attributes: any
}

export interface ApiMeta {
  authors?: string
  count?: number
}

export interface ApiLinks {
  self?: string
  prev?: string
  next?: string
  last?: string
  update?: string
  copyright?: string
  first?: string
}

export interface ApiConfigurationData {
  configuration: string
}

export interface TranslatedApiData {
  _type: string
  _id: string
}

export interface ApiResponse {
  data: ApiData|ApiData[]
  links: ApiLinks
  included: ApiData[]
  meta: ApiMeta
  errors?: ApiError
}

export interface ApiTranslatedResponse {
  data: TranslatedApiData|TranslatedApiData[]|ApiConfigurationData
  links: ApiLinks
  meta: ApiMeta
  status?: number
}

export interface ApiError {
  code: number
  message: string
}

export interface BulkElement {
  url: string
  method: "GET"|"POST"|"PUT"
  parameters: any[]
  id: string
  transformer?: Function
  opt?: ApiParameters
  data: null
}

export interface BulkResponse {
  body: ApiResponse
  id: string
  status: number
}

export interface ApiParameters {
  useCache?: boolean
  useSecondChance?: boolean
  indexSpecs?: boolean
  getBulk?: boolean
  transformer?: Function
}
