import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import {
  RecommendationsResponse,
  RecommendationBundlesResponse,
  SaveRecommendationRequest,
  SaveRecommendationResponse
} from '../models/recommendation.model';

@Injectable({
  providedIn: 'root'
})
export class RecommendationsService {
  constructor(private apiService: ApiService) {}

  getRecommendations(estimationId: number): Observable<RecommendationsResponse> {
    return this.apiService.get<RecommendationsResponse>(`/estimations/${estimationId}/recommendations`);
  }

  saveRecommendation(estimationId: number, data: SaveRecommendationRequest): Observable<SaveRecommendationResponse> {
    return this.apiService.post<SaveRecommendationResponse>(`/estimations/${estimationId}/recommendations`, data);
  }

  getRecommendationBundles(estimationId: number): Observable<RecommendationBundlesResponse> {
    return this.apiService.get<RecommendationBundlesResponse>(`/estimations/${estimationId}/recommendation-bundles`);
  }
}
